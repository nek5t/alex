<?php
/**
 * Plugin Name:       Alex Blocks
 * Description:       Custom Gutenberg blocks for Alex sites.
 * Version:           1.0.0
 * Author:            Eric Phillips
 * Text Domain:       alexblocks
 *
 * @package alex/alex-blocks
 */

/**
 * Register block types from block metadata JSON
 */
function alexblocks_register_block_types() {
	$blocks = glob( plugin_dir_path( __FILE__ ) . 'build/**/block.json', GLOB_NOSORT );

	foreach ( $blocks as $block ) {
		register_block_type( $block );
	}
}
add_action( 'init', 'alexblocks_register_block_types' );
