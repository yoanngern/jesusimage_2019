<?php
function jesusimage_school_theme_enqueue_styles() {

	$parent_style = 'jesusimage_2019-style';

	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css', false, wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'jesusimage_2019-school-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get( 'Version' )
	);
}

add_action( 'wp_enqueue_scripts', 'jesusimage_school_theme_enqueue_styles' );

add_action( 'wp_head', 'wpmy_redirect_logged_in_users_away_from_home' );
function wpmy_redirect_logged_in_users_away_from_home() {
	global $pagenow;

	if ( is_user_logged_in() && ! is_super_admin() && $pagenow != 'wp-signup.php' && ( is_home() || is_front_page() ) ) {
		wp_redirect( '/dashboard' );
		exit;
	}
}


//allow redirection, even if my theme starts to send output to the browser
add_action( 'init', 'do_output_buffer' );
function do_output_buffer() {
	ob_start();
}