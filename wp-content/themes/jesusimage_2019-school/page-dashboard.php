<?php /* Template Name: Dashboard */ ?>

<?php get_header(); ?>

<section id="content">

    <?php if (get_the_post_thumbnail($post)): ?>

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
    while (have_posts()) : the_post(); ?> <!--Because the_content() works only inside a WP Loop -->
        <article class="content-page">
            <div class="platter">

                <?php

                if (!get_the_post_thumbnail($post)):
                    the_title('<h1 class="entry-title">', '</h1>');
                endif;
                ?>



                <?php

                if (is_user_logged_in()) : ?>

                    <?php
                    echo '<div class="content">';
                    the_content();
                    echo '</div>';
                    ?> <!-- Page Content -->


                    <?php

                    $user = get_userdata(get_current_user_id());

                    $app_form = get_field('user_app_form', wp_get_current_user());
                    $app_fee = get_field('user_app_fee', wp_get_current_user());
                    $app_resume = get_field('user_resume', wp_get_current_user());
                    $app_references = get_field('user_pastoral_references', wp_get_current_user());

                    $year = get_field('user_app_year', wp_get_current_user());
                    $app_status = get_field('user_app_status', wp_get_current_user());
                    $student_id = get_field('user_student_id', wp_get_current_user());


                    ?>

                    <div class="floatblock">
                        <table class="dashboard">
                            <thead>
                            <tr>
                                <th colspan="2">My profile</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Firstname</td>
                                <td><?php echo $user->first_name ?></td>
                            </tr>
                            <tr>
                                <td>Lastname</td>
                                <td><?php echo $user->last_name ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><?php echo $user->user_email ?></td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td><?php echo $user->user_login ?></td>
                            </tr>
                            <tr>
                                <td>Student ID</td>
                                <td><?php echo $student_id ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>


                    <?php if ($app_status['value'] != 'accepted' && $app_status['value'] != 'declined'): ?>

                        <div class="floatblock">
                            <table class="dashboard">
                                <thead>
                                <tr>
                                    <th colspan="2">My application</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>School Year</td>
                                    <td><?php echo $year['label'] ?></td>
                                </tr>
                                <tr>
                                    <td>Application Form</td>
                                    <td><?php echo $app_form['label'] ?></td>
                                </tr>
                                <tr>
                                    <td>Application Fee</td>
                                    <td><?php echo $app_fee['label'] ?></td>
                                </tr>

                                <?php if ($app_form['value'] == 'received' && $app_fee['value'] == 'paid' && $year['value'] == '1'): ?>
                                    <tr>
                                        <td>My work history</td>
                                        <td><?php echo $app_resume['label'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Pastoral references</td>
                                        <td><?php echo $app_references['label'] ?></td>
                                    </tr>
                                <?php endif; ?>

                                </tbody>
                            </table>
                        </div>

                        <h2>Next steps</h2>

                        <?php if ($year['value'] == '1'): ?>

                            <?php if ($app_form['value'] == 'received' && $app_fee['value'] == 'paid'): ?>

                                <p>We have received your application form and fee.</p>

                            <?php endif; ?>

                            <?php if ($app_resume['value'] != 'received' || $app_references['value'] != 'received'): ?>

                                <p>Please submit your Pastoral/Leader references and work history.</p>

                            <?php else: ?>
                                <p>We will reach out to schedule your interview.</p>

                            <?php endif; ?>

                            <p style="text-align: center; margin-top: 50px">

                                <?php if ($app_form['value'] != 'received' && get_field('first_app_form')): ?>

                                    <a href="<?php echo get_field('first_app_form'); ?>" class="button">Application
                                        form</a>

                                <?php endif; ?>

                                <?php if ($app_fee['value'] != 'paid' && get_field('first_app_fee')): ?>

                                    <a href="<?php echo get_field('first_app_fee'); ?>" class="button">Application
                                        fee</a>

                                <?php endif; ?>

                                <?php if ($app_form['value'] == 'received' && $app_fee['value'] == 'paid'): ?>

                                    <?php if ($app_resume['value'] != 'received' && get_field('first_app_resume')): ?>

                                        <a href="<?php echo get_field('first_app_resume'); ?>" class="button">Upload my
                                            work history</a>

                                    <?php endif; ?>

                                    <?php if ($app_references['value'] != 'received' && get_field('first_app_pastoral_references')): ?>

                                        <a href="<?php echo get_field('first_app_pastoral_references'); ?>"
                                           class="button">Pastoral references</a>

                                    <?php endif; ?>
                                <?php endif; ?>

                            </p>


                        <?php elseif ($year['value'] == '2'): ?>

                            <p style="text-align: center; margin-top: 50px">

                                <?php if ($app_form['value'] != 'received' && get_field('second_app_form')): ?>

                                    <a href="<?php echo get_field('second_app_form'); ?>" class="button">Application
                                        form</a>

                                <?php endif; ?>

                                <?php if ($app_fee['value'] != 'paid' && get_field('second_app_fee')): ?>

                                    <a href="<?php echo get_field('second_app_fee'); ?>" class="button">Application
                                        fee</a>

                                <?php endif; ?>
                            </p>

                            <?php if ($app_form['value'] == 'received' && $app_fee['value'] == 'paid'): ?>

                                <p>We have received your application form and fee.<br/>
                                    We will reach out to schedule your interview.</p>

                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>

                    <?php

                    $args = array(
                        'posts_per_page' => 50,
                        'post_type' => 'give_forms',
                        'meta_key' => 'student',
                        'meta_query' => array(
                            'key' => 'student', // name of custom field
                            'value' => get_current_user_id(), // matches exaclty "123",
                            'compare' => '='
                        )

                    );

                    $query = new WP_Query($args);

                    $donations = $query->get_posts();

                    ?>

                    <?php if ($app_status['value'] == 'accepted' && $donations):
                        foreach ($donations as $donation):

                            $donation_id = $donation->ID;

                            $amount = give_donation_amount($donation_id);
                            $goal = give_get_form_goal($donation_id);

                            $url = $donation->guid;

                            $title = $donation->post_title;

                            ?>

                            <h2><?php echo $title; ?></h2>

                            <?php give_show_goal_progress($donation_id); ?>

                            <p style="text-align: left"><a href="<?php echo $url; ?>" class="button">Pay now</a></p>

                        <?php

                        endforeach;
                    endif; ?>

                <?php else: ?>

                    <p>Please <a href="/login">login</a> to access this page.</p>

                <?php endif; ?>


        </article><!-- .entry-content-page -->

        </div>
    <?php
    endwhile; //resetting the page loop
    wp_reset_query(); //resetting the page query
    ?>


</section>


<?php get_footer(); ?>

