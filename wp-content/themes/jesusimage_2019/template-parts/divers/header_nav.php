<header>

    <section class="content">
        <div id="burger">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="logo">
            <a href="<?php echo home_url(); ?>" style="background-image: url('<?php echo get_field( 'logo_top', 'option' )['sizes']['logo'] ?>')" id="logo"></a>
        </div>
        <nav>
			<?php

            /*
			wp_nav_menu( array(
				'theme_location' => 'main'
			) );
            */

			if ( is_user_logged_in() ) :
				wp_nav_menu( array(
					'theme_location' => 'private'
				) );
			else:
				wp_nav_menu( array(
					'theme_location' => 'public'
				) );
			endif; ?>
        </nav>
    </section>

</header>

<main>