<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php do_action( 'storefront_before_footer' ); ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="col-full">

			<?php
			/**
			 * Functions hooked in to storefront_footer action
			 *
			 * @hooked storefront_footer_widgets - 10
			 * @hooked storefront_credit         - 20
			 */
			do_action( 'storefront_footer' );
			?>


            <section class="content">

                <?php
                $original_blog_id = get_current_blog_id();
                switch_to_blog(1);
                ?>

                <nav class="footer-menu">

                    <?php

                    wp_nav_menu(array('theme_location' => 'footer1'));
                    wp_nav_menu(array('theme_location' => 'footer2'));
                    wp_nav_menu(array('theme_location' => 'footer3'));
                    wp_nav_menu(array('theme_location' => 'footer4'));

                    ?>

                </nav>

                <div class="info">
                    <div class="logo">
                        <a href="<?php echo home_url(); ?>" id="logo"></a>
                    </div>
                    <div class="text">
                        <p>P.O. Box 950640<br/>
                            Lake Mary, FL 32795</p>
                    </div>
                    <div class="text">
                        <p><a href="tel:+1 407-878-7421">407-878-7421</a>
                            <a href="mailto:info@jesusimage.tv">info@jesusimage.tv</a></p>
                    </div>
                    <div class="text">
                        <p>© 2019 Jesus Image<br/>
                            All Rights Reserved.</p>
                    </div>
                </div>


                <?php
                // Switch back to the current blog
                switch_to_blog($original_blog_id);
                ?>


            </section>

		</div><!-- .col-full -->
	</footer><!-- #colophon -->

	<?php do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
