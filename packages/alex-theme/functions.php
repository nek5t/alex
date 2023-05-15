<?php

if ( ! function_exists( 'alextheme_support' ) ) :
	function alextheme_support() {

		// Adding support for core block visual styles.
		add_theme_support( 'wp-block-styles' );

		// Enqueue editor styles.
		add_editor_style();
	}
	add_action( 'after_setup_theme', 'alextheme_support' );
endif;

/**
 * Enqueue scripts and styles.
 */
function alextheme_scripts() {
	// Enqueue theme stylesheet.
	wp_enqueue_style( 'emptytheme-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme()->get( 'Version' ) );
}

add_action( 'wp_enqueue_scripts', 'alextheme_scripts' );
