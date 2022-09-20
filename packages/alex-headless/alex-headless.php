<?php
/**
 * Plugin Name:       Alex Headless
 * Description:       Enhances support for headless front end(s).
 * Version:           1.0.0
 * Author:            Eric Phillips
 * Text Domain:       alexhless
 */

namespace AlexHeadless\Endpoint;

include_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Symfony\Component\CssSelector\CssSelectorConverter;
use \WP_Query;
use \add_action;
use \add_filter;
use \apply_filters;
use \DOMDocument;
use \DOMXPath;
use \esc_html__;
use \rest_ensure_response;
use \WP_Block_Type_Registry;
use \WP_Error;
use \WP_REST_Response;

define('ALEX_ALLOWED_BLOCKS', array(
	'core/template-part',
	'core/post-title',
	'core/post-content',
    'core/paragraph',
    'core/quote',
    'alexblocks/details'
));

define('ALEX_UNUSED_PROPS', array(
    'innerHTML',
    'innerContent'
));

class AlexHeadless_REST_Controller {
    public function __construct()
    {
        $this->html = new DOMDocument();
        $this->cssConverter = new CssSelectorConverter();
        $this->registered_block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $this->namespace = '/alex/v1';
        $this->route = '/posts';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            $this->route,
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_posts' ),
                'args' => array(
                    'path' => array(
                        'description' => esc_html__( 'The page or post path' ),
                        'type' => 'string',
                        'required' => true
                    )
                )
            ),
        );
    }

    public function get_posts( $request ) {
		global $post;
		global $_wp_current_template_content;
        $accepted_content_types = apply_filters( 'alexhless_content_types', array( 'post', 'page' ) );
		$data = null;

		query_posts(array(
			'name' => $request['path'],
			'post_type' => $accepted_content_types,
			'posts_per_page' => 1,
			'_wp-find-template' => true
		));

		if (have_posts()) {
			ob_start();
			require_once( ABSPATH . WPINC . '/template-loader.php' );
			ob_end_clean();

			while (have_posts()) {
				the_post();
				$data = $post;
				$data->template_content = $_wp_current_template_content;
				$data->blocks = $this->parse_blocks( $data );
			}
		}

		$response = new WP_REST_Response( $data );

		wp_reset_query();

        return rest_ensure_response( $response );
    }

    private function parse_blocks( $post )  {
        return $this->prepare_blocks( parse_blocks( $post->template_content ) );
    }

    private function prepare_blocks($blocks) {
		global $post;
        $blocks = array_filter( $blocks, function($b) { return ! empty( $b['blockName'] ); } );

        foreach( $blocks as &$block ) {
			$name = $block['blockName'];
            $metadata = $this->registered_block_types[$name];
			$isDynamic = !empty($metadata->render_callback) && function_exists($metadata->render_callback);

			$block['dynamic'] = $isDynamic;
			$block['rendered'] = null;

			if (true === $isDynamic) {
				if ('core/template-part' === $name) {
					$template_content = $this->get_template_content($block);
					if ($template_content) {
						$block['innerBlocks'] = parse_blocks( $template_content );
					}


				} elseif ('core/post-content' === $name) {
					$block['innerBlocks'] = parse_blocks( $post->post_content );
				} else {
					$block['rendered'] =  render_block( $block );
				}
			}

            foreach($metadata->attributes as $attr_name => $attr) {
                if (!empty($attr['source'])) {
                    $attr_value = call_user_func_array(
                        array( $this, 'get_source_' . $attr['source'] ),
                        array( $block['innerHTML'], $attr )
                    );

                    $block['attrs'] = array_merge(
                        array( $attr_name => $attr_value ),
                        $block['attrs']
                    );
                }
            }

            $block['innerBlocks'] = $this->prepare_blocks($block['innerBlocks']);

            $unset_props = apply_filters( 'alex_unwanted_props', ALEX_UNUSED_PROPS );

            foreach( $unset_props as $prop ) {
                unset($block[$prop]);
            }
        }

        return array_values( $blocks );
    }

    private function get_source_html($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $selector = $this->cssConverter->toXPath($attr['selector']);
        $nodeList = $xpath->query($selector);
        $context_node = $nodeList->item(0);
        $result = array();

        if ($context_node && !empty($attr['multiline'])) {
            $multi_selector = $this->cssConverter->toXPath($attr['multiline']);
            $lines = $xpath->query($multi_selector, $context_node);

            foreach($lines as $node) {
                array_push($result, $node->nodeValue);
            }
        } else {
            array_push( $result, $context_node?->nodeValue );
        }

        return array_values( array_filter( $result ) );
    }

    private function get_source_attribute($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $attribute = $attr['attribute'];
        $selector = $this->cssConverter->toXPath($attr['selector']);
        $nodeList = $xpath->query($selector);

        return $nodeList
            ->item(0)
            ?->attributes
            ->getNamedItem($attribute)
            ?->nodeValue;
    }

    private function get_source_text($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $selector = $this->cssConverter->toXPath($attr['selector']);
        $nodeList = $xpath->query($selector);

        return $nodeList->item(0)?->textContent;
    }

    private function get_source_query($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $selector = $this->cssConverter->toXPath($attr['selector']);
        $nodeList = $xpath->query($selector);
        $result = array();

        foreach($nodeList as $node) {
            $node_html = $this->save_html($node);

            foreach($attr['query'] as $key => $query) {
                $query_result = call_user_func_array(
                    array( $this, 'get_source_' . $query['source'] ),
                    array( $node_html, $query )
                );
                array_merge(
                    array( $key => $query_result ),
                    $result
                );
            }
        }

        return $result;
    }

    private function save_html($node) {
        return $this->html->saveHTML($node);
    }

    private function load_html($html) {
        $this->html->loadHtml($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR);

        return $this->html;
    }

	private function get_template_content( $template_part ) {
		$attributes = $template_part['attrs'];
		$template_content = '';

		if (
			isset( $attributes['slug'] ) &&
			isset( $attributes['theme'] ) &&
			wp_get_theme()->get_stylesheet() === $attributes['theme']
		) {
			$template_part_id    = $attributes['theme'] . '//' . $attributes['slug'];
			$template_part_query = new WP_Query(
				array(
					'post_type'      => 'wp_template_part',
					'post_status'    => 'publish',
					'post_name__in'  => array( $attributes['slug'] ),
					'tax_query'      => array(
						array(
							'taxonomy' => 'wp_theme',
							'field'    => 'slug',
							'terms'    => $attributes['theme'],
						),
					),
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$template_part_post  = $template_part_query->have_posts() ? $template_part_query->next_post() : null;
		}

		if ($template_part_post) {
			$template_content = $template_part_post->post_content;
		} else {
			// Else, if the template part was provided by the active theme,
			// render the corresponding file content.
			$parent_theme_folders        = get_block_theme_folders( get_template() );
			$child_theme_folders         = get_block_theme_folders( get_stylesheet() );
			$child_theme_part_file_path  = get_theme_file_path( '/' . $child_theme_folders['wp_template_part'] . '/' . $attributes['slug'] . '.html' );
			$parent_theme_part_file_path = get_theme_file_path( '/' . $parent_theme_folders['wp_template_part'] . '/' . $attributes['slug'] . '.html' );
			$template_part_file_path     = 0 === validate_file( $attributes['slug'] ) && file_exists( $child_theme_part_file_path ) ? $child_theme_part_file_path : $parent_theme_part_file_path;

			if ( 0 === validate_file( $attributes['slug'] ) && file_exists( $template_part_file_path ) ) {
				$template_content = file_get_contents( $template_part_file_path );
			}
		}

		return $template_content;
	}
}

function alexhless_register_rest_routes() {
    $controller = new AlexHeadless_REST_Controller();
    $controller->register_routes();
}

add_action( 'rest_api_init', __NAMESPACE__ . '\\alexhless_register_rest_routes' );

function alexhless_filter_block_types() {
    return apply_filters( 'alex_allowed_block_types', ALEX_ALLOWED_BLOCKS );
}
add_filter( 'allowed_block_types', __NAMESPACE__ . '\\alexhless_filter_block_types' );

function alexhless_enqueue_editor_scripts() {
    wp_register_script(
        'alexheadless',
        plugin_dir_url( __FILE__ ) . 'editor-script.js',
        array( 'wp-blocks', 'wp-dom-ready' ),
        false,
        true
    );

    wp_localize_script( 'alexheadless', 'alexhless', array( 'allowedBlocks' => ALEX_ALLOWED_BLOCKS ) );

    wp_enqueue_script( 'alexheadless' );
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\alexhless_enqueue_editor_scripts' );
