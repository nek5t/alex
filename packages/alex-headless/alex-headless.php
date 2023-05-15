<?php
/**
 * Plugin Name:       Alex Headless
 * Description:       Enhances support for headless front end(s).
 * Version:           1.0.0
 * Author:            Eric Phillips
 * Text Domain:       alexhless
 *
 * @package alex/alex-headless
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Alex\\Headless\\Controller' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	define(
		'ALEX_ALLOWED_BLOCKS',
		array(
			'core/paragraph',
			'core/list',
			'core/list-item',
			'core/image', // Necessary to load Columns block.
			'core/columns',
			'core/column',
			'core/quote',
			'alexblocks/details',
		)
	);

	define(
		'ALEX_UNUSED_PROPS',
		array(
			'innerHTML',
			'innerContent',
		)
	);

	/**
	 * Register REST routes to instance of Controller object
	 */
	function alexhless_register_rest_routes() {
		$controller = new \Alex\Headless\Controller();
		$controller->register_routes();
	}

	add_action( 'rest_api_init', 'alexhless_register_rest_routes' );

	/**
	 * Apply filter to allowed block types, so that only supported blocks
	 * can be used in the editor
	 */
	function alexhless_filter_block_types() {
		return apply_filters( 'alex_allowed_block_types', ALEX_ALLOWED_BLOCKS );
	}
	add_filter( 'allowed_block_types', 'alexhless_filter_block_types' );

	/**
	 * Register and enqueue editor scripts
	 */
	function alexhless_enqueue_editor_scripts() {
		wp_register_script(
			'alexheadless',
			plugin_dir_url( __FILE__ ) . 'editor-script.js',
			array( 'wp-blocks', 'wp-dom-ready' ),
			get_plugin_data( __FILE__, false )['Version'],
			true
		);

		wp_localize_script( 'alexheadless', 'alexhless', array( 'allowedBlocks' => ALEX_ALLOWED_BLOCKS ) );

		wp_enqueue_script( 'alexheadless' );
	}
	add_action( 'enqueue_block_editor_assets', 'alexhless_enqueue_editor_scripts' );
}
