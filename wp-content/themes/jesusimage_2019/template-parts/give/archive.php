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


							$title = get_field( 'give_first_name' ) . " " . get_field( 'give_last_name' );

							$url = get_the_permalink();

							?>

                            <tr>
                                <td><?php echo $title; ?></td>
                                <td>1st Year 2018 - 2019</td>
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




