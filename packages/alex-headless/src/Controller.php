<?php

namespace Alex\Headless;

use Alex\Headless\Blocks\Parser;

class Controller
{
	public function __construct()
	{
		$this->namespace = '/alex/v1';
		$this->route = '/posts';
		$this->block_parser = new Parser();
	}

	public function register_routes()
	{
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				'methods' => 'GET',
				'callback' => array($this, 'get_posts'),
				'args' => array(
					'path' => array(
						'description' => esc_html__('The page or post path'),
						'type' => 'string',
						'required' => true
					)
				)
			),
		);
	}

	public function get_posts($request)
	{
		$accepted_content_types = \apply_filters('alexhless_content_types', array('post', 'page'));
		$post = get_page_by_path($request['path'], OBJECT, $accepted_content_types);

		if ($post) {
			$post->blocks = $this->parse_blocks($post);
			$response = new \WP_REST_Response($post);
		} else {
			$response = new \WP_REST_Response(null, 404);
		}

		return rest_ensure_response($response);
	}

	private function parse_blocks($post)
	{
		return $this->block_parser->prepare_blocks(\parse_blocks($post->post_content));
	}
}
