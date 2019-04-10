<div class="global">

    <section class="content">
        <?php

        $_original_blog_id = get_current_blog_id();

        switch_to_blog(1);

        $main_tmp = get_template_directory();


        ?>
        <div id="burger">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="logo">
            <a href="<?php echo home_url(); ?>">
                <?php include($main_tmp . '/template-parts/divers/jesusimage_logo.php'); ?>
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
        switch_to_blog($_original_blog_id);
        ?>
    </section>
</div>