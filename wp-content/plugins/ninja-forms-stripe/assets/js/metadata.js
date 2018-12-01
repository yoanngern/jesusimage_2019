/**
 * Makes sure that metadata keys are not duplicates.
 *
 * @package Ninja Forms builder
 * @subpackage Advanced
 * @copyright (c) 2017 WP Ninjas
 * @since 3.1
 */
var nfStripeMetadataController = Marionette.Object.extend( {
    initialize: function() {
        /*
         * When someone types in the "name" or "eq" portion of our calculation, we need to make sure
         * that they haven't duplicated a name or made a bad EQ reference.
         */
        var nfRadio = Backbone.Radio;
        this.listenTo( nfRadio.channel( 'option-repeater-stripe_metadata' ), 'keyup:option', this.keyUp );
        /*
         * Same thing for when our calculation option is updated
         */
        this.listenTo( nfRadio.channel( 'option-repeater-stripe_metadata' ), 'update:option', this.updateMetadata );
        // Listen for appStart to fire our notice.
//        this.listenTo( nfRadio.channel( 'app' ), 'after:appStart', this.ccFieldNotice );
        this.listenTo( nfRadio.channel( 'app' ), 'replace:fieldKey', this.replaceFieldKey );

	    nfRadio.channel( 'app' ).reply( 'update:hiddenFields', this.removeCreditCardFields );
    },
    
    /**
     * Function to display a notice to admins of Forms using our Credit Card Fields.
     * TODO: Extract this into its own controller later to avoid confusion.
     */
    ccFieldNotice: function() {
        var nfRadio = Backbone.Radio;
        var fields = nfRadio.channel( 'app' ).request( 'get:formModel' ).get( 'fields' ).models;
        var showNotice = false;
        // Loop over our field collection
        _.each( fields, function( field ) {
            // If we have a credit card field of any kind...
            if( -1 < field.get( 'type' ).indexOf( 'creditcard' ) ) {
                // Record it.
                showNotice = true;
                // Add a deprecated class to the model view.
                var target = '#field-' + field.get( 'id' );
                jQuery( target ).addClass( 'deprecated' );
            }
        } );
        // Show a notice if we got results.
        if ( showNotice ) {
            var options = { autoClose: false, closeButton: 'box', closeOnClick: false };
            nfRadio.channel( 'notices' ).request( 'add', 'stripev2', nfStripe.creditCardFieldDeprecation, options );
        }
    },

	removeCreditCardFields: function () {
		var hiddenFields = [ 'creditcard',
			'creditcardcvc',
			'creditcardexpiration',
			'creditcardfullname',
			'creditcardnumber',
			'creditcardzip' ];

		return hiddenFields;
	},

    keyUp: function( e, optionModel ) {
        // Get our current value
        var value = jQuery( e.target ).val();
        var id = jQuery( e.target ).data( 'id' );
        if( 'key' == id ) { // We are editing the key field
            // Check to see if our key already exists.
            this.checkKey( value, optionModel );
        }
    },

    updateMetadata: function( optionModel ) {
        this.checkKey( optionModel.get( 'key' ), optionModel, false );
    },

    /**
     * Check to see if a metadata key exists.
     * 
     * @since  3.1
     * @param  string 			key        key to check
     * @param  backbone.model 	optionModel 
     * @return void
     */
    checkKey: function( key, optionModel, silent ) {
        silent = silent || true;
        // Get our current errors, if any.
        var errors = optionModel.get( 'errors' );
        // Search our metadata collection for our key
        var found = optionModel.collection.where( { key: jQuery.trim( key ) } );
        // If our key exists, add an error to the option model
        if ( 0 != found.length && found[0].get( 'order' ) != optionModel.get( 'order' ) ) {
            errors.keyExists = 'Keys must be unique. Please enter a different key.';
        } else {
            optionModel.set( 'key', key, { silent: silent } );
            delete errors.keyExists;
        }

        optionModel.set( 'errors', errors );
        optionModel.trigger( 'change:errors', optionModel );
    },
    
    /**
     * Listen for field key changes and update our
     * option repeater values as necessary.
     * 
     * @since 3.2
     * @param backbone.model  dataModel     the action model making the call
     * @param backbone.model  keyModel      the field model that was updated
     * @param backbone.model  settingModel  the setting model being passed
     * @return void
     */
    replaceFieldKey: function( dataModel, keyModel, settingModel ) {
        // Referenced our Radio.
        var nfRadio = Backbone.Radio;
        var settingName = settingModel.get( 'name' );
        // If we're not the setting type...
        if ( 'stripe_metadata' != settingName ) return false;
        // OR the action type we're looking for...
        // Exit early.
	    // check for collectpayment and stripe
        if ( 'stripe' != dataModel.get( 'type' ) &&
            'collectpayment' != dataModel.get( 'type' ) ) return false;
        var oldKey = nfRadio.channel( 'app' ).request( 'get:fieldKeyFormat', keyModel._previousAttributes[ 'key' ] );
        var newKey = nfRadio.channel( 'app' ).request( 'get:fieldKeyFormat', keyModel.get( 'key' ) );
        // If the setting has something in it...
        if( 'undefined' != typeof dataModel.get( 'stripe_metadata' ) ) {
            var metaModel = dataModel.get( 'stripe_metadata' );
            // If this is an array...
            if ( Array.isArray( metaModel ) ) {
                metaModel.forEach( function ( model ) {
                    if ( 'string' == typeof model.value && model.value == oldKey ) {
                        model.value = newKey;
                    }
                } );
            } // Otherwise (assume we have a collection)...
            else {
                metaModel.each( function ( model ) {
                    if ( 'string' == typeof model.get( 'value' ) && model.get( 'value' ) == oldKey ) {
                        model.set( 'value', newKey );
                    }
                } );
            }
            dataModel.set( 'stripe_metadata', metaModel );
        }
    }

});

jQuery( document ).ready( function( $ ) {
    new nfStripeMetadataController();
});