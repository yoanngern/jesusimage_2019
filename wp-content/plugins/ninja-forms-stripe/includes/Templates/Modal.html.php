<script type="text/template" id="tmpl-nf-stripe-modal">
    <form>
        <h2><?php _e( 'Please input your Stripe API keys.', 'ninja-forms-stripe' );
        ?></h2>
        <a href="https://stripe.com/docs/dashboard#api-keys" target="_blank">
	        <?php _e( 'Help getting API keys.', 'ninja-forms-stripe' ); ?>
        </a><br />
	    <hr/>
	    <label for="nf-stripe-test-publishable-key-input">
		    <?php _e( 'Test Publishable Key', 'ninja-forms-stripe' ); ?>
	    </label>
        <input type="text" name="nf-stripe-test-publishable-key-input"
           id="nf-stripe-test-publishable-key-input">
        <label for="nf-stripe-test-secret-key-input">
            <?php _e( 'Test Secret Key', 'ninja-forms-stripe' ); ?>
        </label>
        <input type="text" name="nf-stripe-test-secret-key-input"
               id="nf-stripe-test-secret-key-input">
	    <hr/>
	    <label for="nf-stripe-live-publishable-key-input">
		    <?php _e( 'Live Publishable Key', 'ninja-forms-stripe' ); ?>
	    </label>
	    <input type="text" name="nf-stripe-live-publishable-key-input"
	           id="nf-stripe-live-publishable-key-input">
        <label for="nf-stripe-live-secret-key-input">
            <?php _e( 'Live Secret Key', 'ninja-forms-stripe' ); ?>
        </label>
        <input type="text" name="nf-stripe-live-secret-key-input"
               id="nf-stripe-live-secret-key-input">
        <div class="actions">
            <input type="button" value="<?php _e( 'Save', 'ninja-forms-stripe' ); ?>" class="nf-button
            primary
            save">
            <input type="button" value="<?php _e( 'Cancel', 'ninja-forms-stripe' ); ?>" class="nf-button
            secondary cancel pull-right">
            <span class="spinner" style="display:none; visibility:hidden;"></span>
        </div>
    </form><br/>
    <div class="nf-error" style="color: red;"></div>
</script>

<script type="text/template" id="tmpl-nf-stripe-modal-success">
    <h2><?php _e( 'Success!', 'ninja-forms-stripe' ); ?></h2>
    <div class="success">
        <i class="fa fa-check-circle" aria-hidden="true"></i>
        <p><?php _e( 'Your Stripe API keys have been successfully added.', 'ninja-forms-stripe' ); ?>
        </p>
    </div>
    <a href="#" class="close nf-button primary"><?php _e( 'Get Started!', 'ninja-forms-stripe' ); ?>
    </a>
</script>