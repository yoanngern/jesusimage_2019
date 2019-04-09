<?php do_action('storefront_before_site'); ?>

<div id="page" class="hfeed site">
    <?php do_action('storefront_before_header'); ?>

    <header id="masthead" class="site-header" role="banner" style="<?php storefront_header_styles(); ?>">

        <div class="global">

            <section class="content">
                <?php

                $original_blog_id = get_current_blog_id();

                switch_to_blog(1);


                ?>
                <div id="burger">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="logo">
                    <a href="<?php echo home_url(); ?>">
                        <?php get_template_part('template-parts/divers/jesusimage_logo'); ?>
                    </a>
                </div>

                <nav>
                    <?php

                    if (is_user_logged_in()) :
                        wp_nav_menu(array(
                            'theme_location' => 'private'
                        ));
                    else:
                        wp_nav_menu(array(
                            'theme_location' => 'public'
                        ));
                    endif;

                    ?>
                </nav>

                <?php
                // Switch back to the current blog
                switch_to_blog($original_blog_id);
                ?>
            </section>
        </div>

        <?php
        /**
         * Functions hooked into storefront_header action
         *
         * @hooked storefront_header_container                 - 0
         * @hooked storefront_skip_links                       - 5
         * @hooked storefront_social_icons                     - 10
         * @hooked storefront_site_branding                    - 20
         * @hooked storefront_secondary_navigation             - 30
         * @hooked storefront_product_search                   - 40
         * @hooked storefront_header_container_close           - 41
         * @hooked storefront_primary_navigation_wrapper       - 42
         * @hooked storefront_primary_navigation               - 50
         * @hooked storefront_header_cart                      - 60
         * @hooked storefront_primary_navigation_wrapper_close - 68
         */
        do_action('storefront_header');
        ?>

    </header><!-- #masthead -->

    <?php
    /**
     * Functions hooked in to storefront_before_content
     *
     * @hooked storefront_header_widget_region - 10
     * @hooked woocommerce_breadcrumb - 10
     */
    do_action('storefront_before_content');
    ?>

    <div id="content" class="site-content" tabindex="-1">
        <div class="col-full">

            <?php
            do_action('storefront_content_top'); ?>






