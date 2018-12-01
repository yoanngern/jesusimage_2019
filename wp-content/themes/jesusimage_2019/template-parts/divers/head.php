<?php ?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>


	<?php

	global $wp;
	$og_url         = home_url( $wp->request );
	$og_locale      = get_locale();
	$og_name        = get_the_title();
	$og_title       = get_the_title();
	$og_image       = "";
	$og_description = get_bloginfo( 'description' );


	if ( is_single() ) {
		$og_title = get_the_title() . " - " . get_bloginfo( 'title' );

		if ( get_the_excerpt() ) {

			$og_description = strip_tags( get_the_excerpt() );
		}

	} else {

		$og_title = get_the_title() . " - " . get_bloginfo( 'title' );
	}

	if ( is_front_page() ) {
		$og_title = get_bloginfo( 'title' );
	}


	if ( get_field( 'fb_title' ) ) {

		$og_title = get_field( 'fb_title' );
		$og_name  = get_field( 'fb_title' );

	}

	if ( get_field( 'fb_desc' ) ) {

		$og_description = get_field( 'fb_desc' );

	}

	if ( get_field( 'fb_image' ) ) {

		$og_image = get_field( 'fb_image' )['sizes']['full_hd'];

	}

	?>


    <meta charset="<?php bloginfo( 'charset' ); ?>">

    <link rel="canonical" href="<?php echo $og_url ?>"/>

    <title><?php echo $og_title; ?></title>

    <meta name="Description" content="<?php echo $og_description ?>"/>

    <meta property="og:title" content="<?php echo $og_title; ?>"/>
    <meta property="og:description" content="<?php echo $og_description; ?>"/>
    <meta property="og:image" content="<?php echo $og_image; ?>"/>
    <meta property="og:url" content="<?php echo $og_url; ?>"/>
    <meta property="og:locale" content="<?php echo $og_locale; ?>"/>
    <meta property="og:site_name" content="<?php echo $og_name; ?>"/>
    <meta property="og:type" content="website"/>

    <meta name="viewport"
          content="initial-scale=1, width=device-width, minimum-scale=1, user-scalable=no, maximum-scale=1, width=device-width, minimal-ui">
    <link rel="profile" href="http://gmpg.org/xfn/11">


    <link rel="apple-touch-icon" sizes="57x57"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180"
          href="<?php echo get_template_directory_uri(); ?>/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"
          href="<?php echo get_template_directory_uri(); ?>/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32"
          href="<?php echo get_template_directory_uri(); ?>/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96"
          href="<?php echo get_template_directory_uri(); ?>/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16"
          href="<?php echo get_template_directory_uri(); ?>/images/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/images/manifest.json">
    <link rel="mask-icon" href="favicon_hd.svg" color="#BB9446">
    <meta name="msapplication-TileColor" content="#BB9446">
    <meta name="msapplication-TileImage"
          content="<?php echo get_template_directory_uri(); ?>/images/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="https://use.typekit.net/mfv8oyf.css">

	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>

	<?php wp_head(); ?>

	<?php echo get_field( 'script', 'option' ) ?>


	<?php get_template_part( 'template-parts/divers/facebook_pixel' ); ?>

</head>


<?php if ( get_field( 'facebook_event' ) ):
	echo get_field( 'facebook_event' );
endif; ?>
