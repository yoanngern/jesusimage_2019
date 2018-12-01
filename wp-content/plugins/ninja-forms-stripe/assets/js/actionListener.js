var nfRadio = Backbone.Radio;

// Create a modal view for entering keys
var NFStripeView = Marionette.ItemView.extend({
    // HTML Element
    el: '#nfStripe',
    template: '#tmpl-nf-stripe-modal',

    /**
     * Render view on init.
     * @since 3.0
     *
     * @return void
     */
    initialize: function() {
        this.render();
    },

    // Sets up all events to be listened for.
    events: {
        'click .save'   : 'clickSave',
        'click .cancel' : 'clickCancel',
        'click .close'  : 'clickClose'
    },

    /**
     * Send key to database upon save.
     * @since 3.0
     *
     * @param e
     * @return void
     */
    clickSave: function( e ){
        // Use this to check if keys are in a valid format.
        var keys_valid_format = true;
        var error_fields = new Array();

        // Get our API keys from the input elements.
        var test_secret_key = jQuery( this.el )
            .find( '#nf-stripe-test-secret-key-input' )
            .val();
        // Checking the formatting for a string that starts with 'sk_test_'
        if( 0 < test_secret_key.length
            && ! test_secret_key.includes( 'sk_test_', 0 ) ) {
            keys_valid_format = false;
            error_fields.push( '#nf-stripe-test-secret-key-input' );
        }

	    var test_publishable_key = jQuery( this.el )
		    .find( '#nf-stripe-test-publishable-key-input' )
		    .val();

	    // Checking the formatting for a string that starts with 'pk_test_'
	    if( 0 < test_publishable_key.length
            && ! test_publishable_key.includes( 'pk_test_', 0 ) ) {
		    keys_valid_format = false;
		    error_fields.push( '#nf-stripe-test-publishable-key-input' );
	    }

	    var live_secret_key = jQuery( this.el )
		    .find( '#nf-stripe-live-secret-key-input' )
		    .val();

	    // Checking the formatting for a string that starts with 'sk_live_'
	    if( 0 < live_secret_key.length
		    && ! live_secret_key.includes( 'sk_live_', 0 ) ) {
		    keys_valid_format = false;
		    error_fields.push( '#nf-stripe-live-secret-key-input' );
	    }

	    var live_publishable_key = jQuery( this.el )
		    .find( '#nf-stripe-live-publishable-key-input' )
		    .val();

	    // Checking the formatting for a string that starts with 'pk_live_'
	    if( 0 < live_publishable_key.length
		    && ! live_publishable_key.includes( 'pk_live_', 0 ) ) {
		    keys_valid_format = false;
		    error_fields.push( '#nf-stripe-live-publishable-key-input' );
	    }

	    // if keys are valid, send to backend for saving
	    if( keys_valid_format ) {
		    // Build our data object.
		    var data = {
			    action: 'nf_stripe_update_keys',
			    test_secret_key: test_secret_key,
			    test_publishable_key: test_publishable_key,
			    live_secret_key: live_secret_key,
			    live_publishable_key: live_publishable_key,
			    security: nfAdmin.ajaxNonce
		    };

		    // Setting context to be used later.
		    var that = this;


		    jQuery(this.el)
			    .find('.nf-error')
			    .html('');

		    // Disable inputs during save.
		    jQuery(this.el)
			    .find('input')
			    .attr('disabled', true)
			    .addClass('disabled');

		    // Add spinner.
		    jQuery(this.el)
			    .find('.spinner')
			    .css('display', 'block')
                .css('visibility', 'visible');

		    // Sends data to the AJAXURL via post method.
		    jQuery
			    .post(
				    ajaxurl,
				    data,

				    /**
				     * Checks validity of API key and performs action based on result.
				     * @since 3.0
				     *
				     * @param response
				     */
				    function (response) {

					    // If key is valid, then show success message modal.
					    if ("1" === response.valid_key) {

						    // Set hasKeys to "1" so the modal will not popup
						    // again now that we'ved saved the keys
						    nfStripeKeys.hasKeys = "1";

						    that.template = '#tmpl-nf-stripe-modal-success';
						    that.render();

						    // Else show error message
					    } else {
						    // Targets nf-error and displays message.
						    jQuery(that.el)
							    .find('.nf-error')
							    .html(response.message);

						    // Shakes the modal.
						    jQuery('.jBox-container')
							    .effect('shake', {times: 3}, 850);
					    }

					    // Removes the disabled state from the input after save.
					    jQuery(that.el)
						    .find('input')
						    .attr('disabled', false)
						    .removeClass('disabled');

					    // Hides spinner when not processing.
					    jQuery(that.el)
						    .find('.spinner')
						    .css('display', 'none')
                            .css('visibility', 'hidden');
				    },
				    'json'
			    );
	    } else {
		    jQuery(this.el)
			    .find('.nf-error')
			    .html( nfStripeKeys.keyFormatError );

		    _.each( error_fields, function(error_field) {
		    	jQuery( error_field ).css('background-color', '#ff6666' );
		    });
        }
    },

    /**
     * Closes modal, Closes drawer, and stripe remove action.
     * @since 3.0
     *
     * @param e
     * @param el
     * @return void
     */
    clickCancel: function ( e, el ) {
        // Sends request to nfStripeApiModal to close the jBox modal.
        nfRadio.channel( 'nfStripe' )
            .request( 'close:modal' );

        // Sends request to nfStripeApiModal to remove action model.
        nfRadio.channel( 'nfStripe' )
            .request( 'remove:model' );

        // Request to close drawer.
        nfRadio.channel( 'app' )
            .request( 'close:drawer' );

        // Set our nf-error element to an empty string.
        jQuery( this.el )
            .find( '.nf-error' )
            .html( '' );

        // Set our api-inputs to an empty string.
        jQuery( this.el )
            .find( '#nf-stripe-test-secret-key-input' )
            .val( '' );

	    jQuery( this.el )
		    .find( '#nf-stripe-test-publishable-key-input-key-input' )
		    .val( '' );

	    jQuery( this.el )
		    .find( '#nf-stripe-live-secret-key-input' )
		    .val( '' );

	    jQuery( this.el )
		    .find( '#nf-stripe-live-publishable-key-input-key-input' )
		    .val( '' );
    },

    /**
     * Sends request to close modal.
     * @since 3.0
     *
     * @return void
     */
    clickClose: function () {
        // Sends request to nfStripeApiModal to close the jBox modal.
        nfRadio.channel( 'nfStripe' )
            .request( 'close:modal' );
    }
});

// New up NFStripeView class.
var nfStripeView = new NFStripeView();

/**
 * Stripe Api Modal handling class.
 * @since 3.0
 *
 * @return void
 */
var nfStripeApiModal = Marionette.Object.extend( {
    modal: false,
    foundStripeAction: false,

    /**
     * Inits our class.
     * @since 3.0
     *
     * @return 3.0
     */
    initialize: function() {
        // Listens to the action channel for a Stripe action and inits
        // the action model when it gets a reply.
	    this.listenTo( nfRadio.channel( 'app' ), 'before:renderSetting', this.getSettingModal );

        // Sends reply to Close Stripe modal.
        nfRadio.channel( 'nfStripe' )
            .reply( 'close:modal', this.closeModal, this );

        // Sends reply to remove action modal.
        nfRadio.channel( 'nfStripe' )
            .reply( 'remove:model', this.removeModel, this );
    },
	getSettingModal: function ( settingModel, dataModel, view ) {
        var type = dataModel.get( 'payment_gateways' );
        var group = settingModel.get( 'group' );

        if( 'stripe' === type && 'primary' === group && ! this.foundStripeAction) {
            this.foundStripeAction = true;
            this.listenToAction( dataModel );
        }
    },
    /**
     * Listens to the action channel and fires Stripe API key modal if
     * API keys aren't present.
     * @since 3.0
     *
     * @param object model
     * @return void
     */
    listenToAction: function( model ) {

        // Check if the Stripe Keys are not valid(equals 0)
        if( "1" != nfStripeKeys.hasKeys ) {
            this.modal = new jBox('Modal', {
                closeOnEsc: false,
                closeOnClick: false,
                closeButton: false,
                content: jQuery( '#nfStripe' ),
                zIndex: 999999999,
                width: 400,
                onOpen: function() {
                    jQuery( '#nf-stripe-test-secret-key-input' ).focus();
                }
            });

            // Open the modal and set the action model to the model that is
            // passed into the function.
            this.modal.open();
            this.modal.actionModel = model;
        }
    },

    /**
     * Closes modal
     * @since 3.0
     *
     * @return void
     */
    closeModal: function() {
        //reset the foundStripeVariable in case they removed the action and try
        // to add another one
        this.foundStripeAction = false;

        this.modal
            .close();
    },

    /**
     * Removes action model
     * @since 3.0
     *
     * @return void
     */
    removeModel: function() {

        // If there was an existing stripe payment action, and the user
        // removed the Stripe API keys, don't remove the existing action.
        // However, if this is a newly created action the user closes the
        // API key modal, then remove the action
        if( ! nfStripeKeys.hasStripeAction ) {
	        nfRadio.channel('actions')
		        .request('delete', this.modal.actionModel);
        }
    }
});

// Fires Stripe Api Modal class on doc.ready
jQuery( document ).ready( function( $ ) {
    new nfStripeApiModal();
});