<?php
/**
 * Created by PhpStorm.
 * User: yoanngern
 * Date: 2019-02-05
 * Time: 14:53
 */


?>


<section id="content">


    <div class="platter">


		<?php


		if ( have_posts() ) : ?>


            <section id="listOfGive">
                <article class="content-page">

                    <h1>Student donation list</h1>

                    <table class="give">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>School Year</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

						<?php

						/* Start the Loop */
						while ( have_posts() ) :
							the_post();

							$person_id = get_field( 'student' );

							$person = get_userdata( $person_id );

							$title = $person->first_name . " " . $person->last_name;

							$year = get_field( 'user_app_year', $person );

							$url = get_the_permalink();

							?>

                            <tr>
                                <td><?php echo $title; ?></td>
                                <td><?php echo $year['label']; ?></td>
                                <td><a class="button" href="<?php echo $url ?>">Donate</a></td>
                            </tr>


						<?php endwhile; ?>

                        </tbody>

                    </table>

                </article>
            </section>


            <nav class="nav">
                <div class="previous"><?php previous_posts_link( __( 'Previous', 'ji_2019' ) ); ?></div>
                <div class="next"><?php next_posts_link( __( 'Next', 'ji_2019' ) ); ?></div>
            </nav>

		<?php

		else :

			get_template_part( 'template-parts/give/none' );

		endif;
		?>

    </div>


</section>


<?php get_footer(); ?>




