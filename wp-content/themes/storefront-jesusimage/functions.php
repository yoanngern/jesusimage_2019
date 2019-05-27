<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

function sf_child_theme_enqueue_script() {
    wp_enqueue_script( 'storefront-js', get_stylesheet_directory_uri() . '/js/main_v1.min.js', false );
}

add_action( 'wp_enqueue_scripts', 'sf_child_theme_enqueue_script' );

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */


add_theme_support( 'woocommerce', array(
	'thumbnail_image_width' => 200,
	'gallery_thumbnail_image_width' => 100,
	'single_image_width' => 500,
) );






add_action( 'init', 'remove_my_actions');
function remove_my_actions() {
	remove_action( 'storefront_header', 'storefront_site_branding',20 );
	remove_action( 'storefront_header', 'storefront_product_search',40 );
    remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
    remove_action( 'storefront_header', 'storefront_header_cart', 60 );

    remove_action('storefront_header', 'storefront_menu_toggle', 20);

    remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );

    remove_action( 'storefront_footer', 'storefront_credit',20 );

	remove_action('wp_footer', 'yd_wpmuso_linkware');


    remove_theme_support( 'wc-product-gallery-zoom' );
    //remove_theme_support( 'wc-product-gallery-lightbox' );
    remove_theme_support( 'wc-product-gallery-slider' );

}


add_action( "customize_register", "ruth_sherman_theme_customize_register" );
function ruth_sherman_theme_customize_register( $wp_customize ) {


	//$wp_customize->remove_section('header_image');

}

/*
function storefront_site_branding() { ?>
	<div style="clear: both; text-align: right;">
		Have questions about our products? <em>Give us a call:</em> <strong>0800 123 456</strong>
	</div>
	<?php
}
*/
