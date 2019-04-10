<header>

    <?php get_template_part('template-parts/divers/header_global'); ?>

    <div class="local">
        <section class="content">
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
        </section>
    </div>

</header>

<main>