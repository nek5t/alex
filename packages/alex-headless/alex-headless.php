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
use add_action;
use add_filter;
use apply_filters;
use DOMDocument;
use DOMXPath;
use esc_html__;
use rest_ensure_response;
use WP_Block_Type_Registry;
use WP_Error;
use WP_REST_Response;

define('ALEX_ALLOWED_BLOCKS', array(
    'core/paragraph',
	'core/list',
	'core/list-item',
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
        $accepted_content_types = apply_filters( 'alexhless_content_types', array( 'post', 'page' ) );
        $post = get_page_by_path( $request['path'], OBJECT, $accepted_content_types );

        if ($post) {
            $post->blocks = $this->parse_blocks( $post );
            $response = new WP_REST_Response( $post );
        } else {
            $response = new WP_REST_Response(null, 404);
        }

        return rest_ensure_response( $response );
    }

    private function parse_blocks( $post )  {
        return $this->prepare_blocks( parse_blocks( $post->post_content ) );
    }

    private function prepare_blocks($blocks) {
        $blocks = array_filter( $blocks, function($b) { return ! empty( $b['blockName'] ); } );

        foreach( $blocks as &$block ) {
            $metadata = $this->registered_block_types[$block['blockName']];

            foreach($metadata->attributes as $attr_name => $attr) {
				$value = null;
				$hasValue = isset($block['attrs'][$attr_name]);

				if ($hasValue) {
					$value = $block['attrs'][$attr_name];
				}

                if (!empty($attr['source'])) {
                    $attr_value = call_user_func_array(
                        array( $this, 'get_source_' . $attr['source'] ),
                        array( $block['innerHTML'], $attr )
                    );

					$value = $attr_value;
                }

				if (null === $value && isset($attr['default'])) {
					$value = $attr['default'];
				}

				$block['attrs'] = array_merge(
					$block['attrs'],
					array( $attr_name => $value )
				);
			}

			$block['attrs'] = array_filter($block['attrs'], function($v) { return null !== $v; });

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
                array_push($result, $this->innerHTML($node));
            }
        } else {
            array_push( $result, $this->innerHTML($context_node));
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

	private function innerHTML($node) {
		$html = '';
		$childNodes = $node?->childNodes ?: array();

		foreach($childNodes as $childNode) {
			$html .= $this->save_html($childNode);
		}

		return $html;
	}

    private function load_html($html) {
        $this->html->loadHtml($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR);

        return $this->html;
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
