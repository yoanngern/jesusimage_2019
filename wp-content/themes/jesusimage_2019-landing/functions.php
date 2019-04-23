<?php
function jesusimage_landing_theme_enqueue_styles() {

	$parent_style = 'jesusimage_2019-style';

	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css', false, wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'jesusimage_2019-landing-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get( 'Version' )
	);
}

add_action( 'wp_enqueue_scripts', 'jesusimage_landing_theme_enqueue_styles' );
