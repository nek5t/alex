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
use apply_filters;
use DOMDocument;
use DOMXPath;
use esc_html__;
use rest_ensure_response;
use WP_Block_Type_Registry;
use WP_Error;
use WP_REST_Response;

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
        }

        return $blocks;
    }

    private function get_source_html($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $selector = $this->cssConverter->toXPath($attr['selector']);
        $nodeList = $xpath->query($selector);

        return $nodeList->item(0)?->nodeValue;
    }

    private function get_source_attribute($html, $attr) {
        $xpath = new DOMXPath( $this->load_html($html) );
        $attribute = $attr['attribute'];
        $selector = $this->cssConverter($attr['selector']);
        $selector .= "/@$attribute";
        $nodeList = $xpath->query($selector);

        return $nodeList->item(0)?->nodeValue;
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
        $nodeList = $this->query($selector);
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
        $this->html->loadHtml($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        return $this->html;
    }
}

function alexhless_register_rest_routes() {
    $controller = new AlexHeadless_REST_Controller();
    $controller->register_routes();
}

add_action( 'rest_api_init', __NAMESPACE__ . '\\alexhless_register_rest_routes' );