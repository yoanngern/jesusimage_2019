<?php /* Template Name: Dashboard */ ?>

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


				<?php

				$user = get_userdata( get_current_user_id() );

				$app_form   = get_field( 'user_app_form', wp_get_current_user() );
				$app_fee    = get_field( 'user_app_fee', wp_get_current_user() );
				$year       = get_field( 'user_app_year', wp_get_current_user() );
				$app_status = get_field( 'user_app_status', wp_get_current_user() );
				$student_id = get_field( 'user_student_id', wp_get_current_user() );


				?>

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





				<?php if ( $app_status['value'] != 'accepted' && $app_status['value'] != 'declined' ): ?>

                    <table class="dashboard">
                        <thead>
                        <tr>
                            <th colspan="2">My application</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Application Form</td>
                            <td><?php echo $app_form['label'] ?></td>
                        </tr>
                        <tr>
                            <td>Application Fee</td>
                            <td><?php echo $app_fee['label'] ?></td>
                        </tr>
                        <tr>
                            <td>Year</td>
                            <td><?php echo $year['label'] ?></td>
                        </tr>
                        </tbody>
                    </table>

                    <h2>Next step</h2>

					<?php if ( $year['value'] == '1' ): ?>

                        <p style="text-align: center; margin-top: 50px">

							<?php if ( $app_form['value'] != 'received' && get_field( 'first_app_form' ) ): ?>

                                <a href="<?php echo get_field( 'first_app_form' ); ?>" class="button">Application
                                    form</a>

							<?php endif; ?>

							<?php if ( $app_fee['value'] != 'paid' && get_field( 'first_app_fee' ) ): ?>

                                <a href="<?php echo get_field( 'first_app_fee' ); ?>" class="button">Application fee</a>

							<?php endif; ?>
                        </p>

						<?php if ( $app_form['value'] == 'received' && $app_fee['value'] == 'paid' ): ?>

                            <p>We have received your application form and fee.<br/>
                                Please submit your pastoral references/Leader endorsements and resume to
                                info@jesusschool.tv.
                                Once these documents are received by our staff we will reach out to schedule your
                                interview.</p>

						<?php endif; ?>

					<?php elseif ( $year['value'] == '2' ): ?>

                        <p style="text-align: center; margin-top: 50px">

							<?php if ( $app_form['value'] != 'received' && get_field( 'second_app_form' ) ): ?>

                                <a href="<?php echo get_field( 'second_app_form' ); ?>" class="button">Application
                                    form</a>

							<?php endif; ?>

							<?php if ( $app_fee['value'] != 'paid' && get_field( 'second_app_fee' ) ): ?>

                                <a href="<?php echo get_field( 'second_app_fee' ); ?>" class="button">Application
                                    fee</a>

							<?php endif; ?>
                        </p>

						<?php if ( $app_form['value'] == 'received' && $app_fee['value'] == 'paid' ): ?>

                            <p>We have received your application form and fee.<br/>
                                We will reach out to schedule your interview.</p>

						<?php endif; ?>

					<?php endif; ?>
				<?php endif; ?>

				<?php

				$donations = get_posts( array(
					'post_type'  => 'give_forms',
					'meta_query' => array(
						'key'     => 'student', // name of custom field
						'value'   => '"' . get_current_user_id() . '"', // matches exaclty "123",
						'compare' => 'LIKE'
					)
				) );


				?>

                <?php if ( $app_status['value'] == 'accepted' && $donations): ?>

					<?php
					$donation = $donations[0];

					$donation_id = $donation->ID;

					$amount = give_donation_amount( $donation_id );
					$goal   = give_get_form_goal( $donation_id );

					$url = $donation->guid;

					?>

                    <h2>My Tuition</h2>

					<?php give_show_goal_progress( $donation_id ); ?>

                    <p style="text-align: center"><a href="<?php echo $url; ?>" class="button">Pay my tuition</a></p>
				<?php endif; ?>

        </article><!-- .entry-content-page -->

        </div>
	<?php
	endwhile; //resetting the page loop
	wp_reset_query(); //resetting the page query
	?>


</section>


<?php get_footer(); ?>

