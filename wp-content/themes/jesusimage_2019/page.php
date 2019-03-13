<?php get_header(); ?>

<section id="content">

	<?php if ( get_the_post_thumbnail( $post ) ): ?>

        <article class="title">
            <div class="image"
                 style="background-image: url('<?php get_field('bg_image')['sizes']['header']; ?>')"></div>
            <div class="title">


                <h1 class="page-title">
                    <span class="txt"><?php echo get_the_title(); ?></span>
                </h1>


            </div>

        </article>

	<?php endif; ?>

	<?php
	// TO SHOW THE PAGE CONTENTS
	while ( have_posts() ) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
        <article class="content-page">
            <div class="platter">


				<?php

				if ( ! get_the_post_thumbnail( $post ) ):
					the_title( '<h1 class="entry-title">', '</h1>' );
				endif;
				?>

				<?php
				echo '<div class="content">';
				the_content();
				echo '</div>';
				?> <!-- Page Content -->

        </article><!-- .entry-content-page -->

        </div>
	<?php
	endwhile; //resetting the page loop
	wp_reset_query(); //resetting the page query
	?>


</section>


<?php get_footer(); ?>

