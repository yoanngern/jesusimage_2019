</main>

<footer>


    <section class="content">
        <div class="logo">
            <a href="<?php echo home_url(); ?>" style="background-image: url('<?php echo get_field( 'logo_bottom', 'option' )['sizes']['footer_logo'] ?>')" id="logo"></a>
            <div id="tagline">
                <span><?php echo get_field( 'footer_tagline', 'option' ) ?></span>
            </div>
        </div>


    </section>


</footer>

<?php wp_footer(); ?>


</body>
</html>
