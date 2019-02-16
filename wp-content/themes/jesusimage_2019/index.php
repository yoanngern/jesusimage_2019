<?php get_header(); ?>

<section id="content">

    <div class="platter">

		<?php

		$post_type = "";

		if ( get_queried_object() instanceof WP_Post_Type ) {

			$post_type = get_queried_object()->name;

		} else {

			$post_type = get_post_type( $_POST );

		}

		$is_elementor_theme_exist = function_exists( 'elementor_theme_do_location' );

		if ( is_singular() ) {
			if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'single' ) ) {
				get_template_part( 'template-parts/single' );
			}
		} elseif ( is_archive() || is_home() ) {
			if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'archive' ) ) {

				if ( $post_type == "give_forms" ) {

					get_template_part( 'template-parts/give/archive' );


				} else {

					get_template_part( 'template-parts/blog/archive' );

				}

			}
		} elseif ( is_search() ) {
			if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'archive' ) ) {
				get_template_part( 'template-parts/search' );
			}
		} else {
			if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'single' ) ) {
				get_template_part( 'template-parts/404' );
			}
		}

		?>

    </div>

</section>

<?php get_footer(); ?>



