<?php /* Template Name: Events */ ?>

<?php get_header(); ?>

<section id="content">

    <?php if (get_field('bg_image')): ?>

        <article class="title">
            <div class="image"
                 style="background-image: url('<?php echo get_field('bg_image')['sizes']['header']; ?>')"></div>
            <div class="title">


                <h1 class="page-title">
                    <span class="txt"><?php echo get_the_title(); ?></span>
                </h1>


            </div>

        </article>

    <?php endif; ?>

    <?php
    // TO SHOW THE PAGE CONTENTS
    while (have_posts()) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
        <article class="content-page">
            <div class="platter">


                <?php

                if (!get_field('bg_image')):
                    the_title('<h1 class="entry-title">', '</h1>');
                endif;
                ?>

                <?php
                echo '<div class="content">';
                the_content();
                echo '</div>';
                ?> <!-- Page Content -->


                <?php

                $today = date('Y-m-d H:i:s');

                $events = wp_get_recent_posts(array(
                    'numberposts' => 10,
                    'offset' => 0,
                    'orderby' => 'meta_value',
                    'meta_key' => 'start',
                    'order' => 'asc',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'end',
                            'compare' => '>=',
                            'value' => $today,
                        )
                    ),
                    'post_type' => 'ji_event',
                    'suppress_filters' => true

                ), OBJECT);

                if ($events != null) : ?>

                    <section class="event_list">
                        <h1><?php _e('Itinerary', 'jesusimage_2019') ?></h1>

                        <ul class="events">
                            <?php foreach ($events as $event) :


                                set_query_var('event', $event);
                                get_template_part('template-parts/event/item');

                            endforeach; ?>
                        </ul>
                    </section>

                <?php endif; ?>


        </article><!-- .entry-content-page -->

        </div>
    <?php
    endwhile; //resetting the page loop
    wp_reset_query(); //resetting the page query
    ?>

</section>


<?php get_footer(); ?>

