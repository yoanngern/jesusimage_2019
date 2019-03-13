<?php get_header(); ?>

<section id="content">

	<?php
	// TO SHOW THE PAGE CONTENTS
	while ( have_posts() ) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
		<article class="content-page">
			<div class="platter">


				<?php
				echo '<div class="content">';
				get_template_part( 'template-parts/give/single-give-form' );
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

