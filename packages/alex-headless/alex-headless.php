<?php
/**
 * Plugin Name:       Alex Headless
 * Description:       Enhances support for headless front end(s).
 * Version:           1.0.0
 * Author:            Eric Phillips
 * Text Domain:       alexhless
 */

namespace AlexHeadless\Endpoint;

use add_action;
use apply_filters;
use esc_html__;
use rest_ensure_response;
use WP_Error;
use WP_REST_Response;

class AlexHeadless_REST_Controller {
    public function __construct()
    {
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
        $response = new WP_REST_Response( $post );

        return rest_ensure_response( $response );
    }
}

function alexhless_register_rest_routes() {
    $controller = new AlexHeadless_REST_Controller();
    $controller->register_routes();
}

add_action( 'rest_api_init', __NAMESPACE__ . '\\alexhless_register_rest_routes' );