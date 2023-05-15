<?php
/**
 * REST Controller class definition
 *
 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/
 *
 * @package alex/alex-headless
 */

namespace Alex\Headless;

use Alex\Headless\Blocks\Parser;

/**
 * Register a REST namespace and routes for consumption by headless front-ends
 */
class Controller {

	/**
	 * Setup namespace, route, and dependencies
	 */
	public function __construct() {
		$this->namespace    = '/alex/v1';
		$this->route        = '/posts';
		$this->block_parser = new Parser();
	}

	/**
	 * Register a new WordPress REST route
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_posts' ),
				'args'     => array(
					'path' => array(
						'description' => esc_html__( 'The page or post path', 'alex' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
			),
		);
	}

	/**
	 * Callback function for /posts route.
	 *
	 * Accepts a relative URL as a parameter
	 * and retrieves post block content as structured data.
	 *
	 * @param WP_REST_Request $request  The REST request object.
	 * @return WP_REST_Response $response
	 */
	public function get_posts( $request ) {
		$accepted_content_types = \apply_filters( 'alexhless_content_types', array( 'post', 'page' ) );
		$post                   = get_page_by_path( $request['path'], OBJECT, $accepted_content_types );

		if ( $post ) {
			$post->blocks = $this->parse_blocks( $post );
			$response     = new \WP_REST_Response( $post );
		} else {
			$response = new \WP_REST_Response( null, 404 );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Parse and process block content for a given post
	 *
	 * @param WP_Post $post     The WordPress post to parse for block content.
	 * @return array            The parsed blocks, each as an associative array
	 *                          of attributes and their values.
	 */
	private function parse_blocks( $post ) {
		return $this->block_parser->prepare_blocks( \parse_blocks( $post->post_content ) );
	}
}
