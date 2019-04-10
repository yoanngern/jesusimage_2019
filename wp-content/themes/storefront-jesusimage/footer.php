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

<?php do_action('storefront_before_footer'); ?>

<footer id="shop_footer">

    <?php

    $original_blog_id = get_current_blog_id();

    switch_to_blog(1);

    $main_tmp = get_template_directory();

    include($main_tmp . '/template-parts/divers/footer_global.php');

    // Switch back to the current blog
    switch_to_blog($original_blog_id);
    ?>

</footer>

<?php do_action('storefront_after_footer'); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
