var nfStripeController = Marionette.Object.extend({
    /**
     * Hook up our render and submit listeners.
     * @since  3.0.0
     * @return void
     */
    initialize: function() {
        this.listenTo( nfRadio.channel( 'form' ), 'render:view', this.initStripe );
        // this.listenTo( nfRadio.channel( 'app' ), 'after:loadControllers', this.initStripe );
        this.listenTo( nfRadio.channel( 'forms' ), 'submit:response', this.submitErrors );
        this.listenTo( nfRadio.channel( 'fields' ), 'change:modelValue', this.removeFieldError );
    },

    /**
     * When our form is rendered, if it is a Stripe form, setup traditional Stripe or Checkout
     * @since  3.0.0
     * @param  {Backbone.view}      layoutView       Form layout view.
     * @return void
     */
    initStripe: function( layoutView ) {
        var that = this;

        if( 'undefined' == typeof Stripe ) return false;
        if( 'undefined' == typeof nfStripe ) return false;

        var formModel = layoutView.model;
        /*
         * If this form isn't in our nfStripe.forms list, then bail.
         */
        var stripeData = _.findWhere( nfStripe.forms, { id: formModel.get( 'id' ) } );
        if ( 'undefined' == typeof stripeData ) return false;

        var active = false;
        var lastAction = false;
        _.each( stripeData.actions, function( action ) {
            if ( ! active ) {
                var request = Backbone.Radio.channel( 'actions' ).request( 'get:status', action.id );
                active = ( 'undefined' != typeof request ) ? request : true;
                lastAction = action;
            }
        } );

        /*
         * If we don't have any credit card fields, then we want to use Checkout.
         */
        var ccNumber = formModel.get( 'fields' ).findWhere( { type: 'creditcardnumber' } );

        /*
         * Register a listener for this form so that we can interrupt submission to get a stripe token.
         */
        Backbone.Radio.channel( 'form-' + formModel.get( 'id' ) ).reply( 'maybe:submit', this.beforeSubmit, this, formModel );

        if ( 'undefined' == typeof ccNumber ) { // No credit card fields
            /*
             * Setup our checkout handler.
             */
            var stripeHandler = StripeCheckout.configure( {
              key: nfStripe.publishable_key,
              image: lastAction.logo,
              locale: 'auto',
              panelLabel: lastAction.label,
              /*
               * token is the successful creation callback.
               *
               * There isn't an error callback because errors are handled in the modal.
               */
              token: function( token, args ) {
                that.tokenSuccess( token, formModel );
              },

              closed: function() {
                /*
                 * If we don't have a token, then we've cancelled.
                 */
                var stripeToken = nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'get:extra', 'stripe_token' );
                if ( 'undefined' == typeof stripeToken ) {
                    nfRadio.channel( 'forms' ).trigger( 'submit:failed', formModel );
                    nfRadio.channel( 'form-' + formModel.get( 'id' ) ).trigger( 'submit:failed', formModel );
                }
              }
            } );

            /*
             * Create our total object.
             */
            that.total = lastAction.total;
            // Record if it's a calc
            that.totalIsCalc = false;
            if ( -1 != this.total.indexOf('{calc:') ) that.totalIsCalc = true;
            // Record if it's a field
            that.totalIsField = false;
            if ( -1 != this.total.indexOf('{field:') ) that.totalIsField = true;
            // Strip the merge tag settings away.
            if ( that.totalIsCalc ) {
                that.total = that.total.replace( '{calc:', '' ).replace( '}', '' );
            } else if ( that.totalIsField ) {
                that.total = that.total.replace( '{field:', '' ).replace( '}', '' );
            }

            formModel.set( 'stripeHandler', stripeHandler );
        } else { // We have credit card fields.
            /*
             * Setup traditional Stripe processing.
             */
            Stripe.setPublishableKey( nfStripe.publishable_key );
            this.listenTo( nfRadio.channel( 'forms' ), 'submit:response', this.maybeClearTokens );
            this.listenTo( ccNumber, 'change:visible', this.removeAllErrors );
        }
    },

    /**
     * We might want to interrupt our submission process for Stripe processing.
     * @since  3.0
     * @param  {Backbone.model}     formModel
     * @return {bool}               false - interrupt submission|true - continue submission.
     */
    beforeSubmit: function( formModel ) {
        console.log(formModel.get('errors').length);
        var stripeData = _.findWhere( nfStripe.forms, { id: formModel.get( 'id' ) } );

        /*
         * Loop through our stripe actions and make sure that they aren't all deactivated.
         */
        var active = false;
        var activeAction = false;
        _.each( stripeData.actions, function( action ) {
            if ( ! active ) {
                var request = Backbone.Radio.channel( 'actions' ).request( 'get:status', action.id );

                    // Save Progress Integration. Check if the action is enabled for Saves.
                    var actionSave = Backbone.Radio.channel( 'actions-' + action.id ).request( 'get:status', request );
                    if( 'undefined' !== typeof actionSave ) request = actionSave;

                active = ( 'undefined' != typeof request ) ? request : true;
                if ( active ) {
                    activeAction = action;
                }
            }
        } );

        /*
        * If we have no active stripe actions, continue with submission.
        */
        if ( ! active ) return true;

        /*
         * If a Stripe Token already exists, continue with submission.
         */ 
        if( formModel.getExtra( 'stripe_token' ) ) return true;

        /*
         * Figure out if we are using Traditional or Checkout.
         * If we don't have any credit card fields, then we want to use Checkout.
         */
        var ccNumber = formModel.get( 'fields' ).findWhere( { type: 'creditcardnumber' } );

        if ( 'undefined' == typeof ccNumber ) { // Checkout
            
            /*
             * If we have errors
             */
            if( 0 < formModel.get( 'errors' ).length ) {
                /*
                 * Tell our form that we've failed submission so that everything resets.
                 */
                nfRadio.channel( 'forms' ).trigger( 'submit:failed', formModel );
                nfRadio.channel( 'form-' + formModel.get( 'id' ) ).trigger( 'submit:failed', formModel );
                return false;
            }

            var stripeHandler = formModel.get( 'stripeHandler' );
            // set a default email value
            var stripeEmail = '';

            // check if Stripe action has a customer email value set
            if( 'undefined' !== typeof activeAction.email && 0 < activeAction.email.length ) {
                // is the customer email a field merge tag
                if ( '{' === activeAction.email.charAt( 0 ) && '}' ===
                    activeAction.email.charAt( activeAction.email.length - 1 ) ) {
                    // Get the field key from the field merge tag
                    var tmpKey = activeAction.email.substring( 1, activeAction.email.length - 1 );
                    var emailArray = tmpKey.split( ':' );
                    // loop through fields to see if we have an email field
                    // that matches the key for the Stripe setting
	                formModel.get( 'fields' ).each( function ( field ) {
		                if ( "email" === field.get( 'type' ) ) {
		                    // the field is an email type and the keys match
			                if ( 0 < emailArray[ 1 ].length &&
                                field.get( 'key' ) === emailArray[ 1 ] ) {
			                    // set the default email value and break loop
				                stripeEmail = field.get( 'value' );
				                return false;
			                }
		                }
	                });
                }
            }
            // total needs to use the current active actions's total
            this.total = activeAction.total;

            // Record if total is a calc
	        this.totalIsCalc = false;
	        if ( -1 != this.total.indexOf('{calc:') ) this.totalIsCalc = true;
	        // Record if it's a field
	        this.totalIsField = false;
	        if ( -1 != this.total.indexOf('{field:') ) this.totalIsField = true;

            // Set the amount value.
            if ( this.totalIsCalc ) {
	            this.total = this.total.replace( '{calc:', '' ).replace( '}', '' );
                var total = nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'get:calc', this.total ).get( 'value' ) * 100;
            } else if ( this.totalIsField ) {
	            this.total = this.total.replace( '{field:', '' ).replace( '}', '' );
                var total = nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'get:fieldByKey', this.total ).get( 'value' ) * 100;
            } else {
                var total = this.total * 100;
            }
            
            // If our total is 0...
            // AND If we don't have a plan...
            // Exit early.
            if ( total == 0 && ! activeAction.plan ) return true;

            var stripeSettings = {
                name: activeAction.title,
                description: activeAction.sub_title,
                zipCode: true,
                email: stripeEmail,
                bitcoin: ( "1" == activeAction.bitcoin ) ? true : false
            };
            // If we were told to include an ammount...
            if ( -1 != activeAction.label.indexOf( '{{amount}}' ) ) {
                // Do so.
                stripeSettings.amount = total;
                stripeSettings.currency = activeAction.currency;
            }

            formModel.get( 'stripeHandler' ).open( stripeSettings );

            return false;
        } else { // Traditional
            Backbone.Radio.channel( 'stripe' ).request( 'remove:errors' );
            Backbone.Radio.channel( 'form-' + formModel.get( 'id' ) ).request( 'remove:error', 'stripe' );

            formModel.get( 'fields' ).each( function( field ){
                nfRadio.channel( 'fields' ).request( 'remove:error', field.get( 'id' ), 'stripe' );
            });

            // If the credit cards aren't present or are not visible, continue submission processing.
            if ( ! ccNumber.get( 'visible' ) ) return true;

            /*
             * The traditional processing controller takes the formModel and this object for context and calling tokenResponse.
             */
            this.processingController = new nfStripeProcessingController( formModel, this );
        }

        // Halt form submission.
        return false;
    },

    /**
     * Resets stripe token when we get a response from the server.
     * @since  3.0.0.
     * @return void
     */
    submitErrors: function( response, textStatus, jqXHR, formID ) {
        nfRadio.channel( 'form-' + formID ).request( 'remove:extra', 'stripe_token' );
    },
    
    removeFieldError: function( model ) {
        var stripeFields = [
            'creditcardfullname',
            'creditcardnumber',
            'creditcardcvc',
            'creditcardexpiration',
            'creditcardzip'
        ];
        if( model.get('errors').length > 0 && stripeFields.includes( model.get( 'type' ) ) ) {
            nfRadio.channel( 'fields' ).request( 'remove:error', model.get( 'id' ), 'stripe' );
        }
    },

    /**
     * If we have stripe errors, then reset our stripe token.
     * @since  3.0
     * @return void
     */
    maybeClearTokens: function( response, textStatus, jqXHR, formID ) {

        if( 'undefined' == typeof response.errors.form ) return;
        if( 'undefined' == typeof response.errors.form.stripe ) return;

        nfRadio.channel( 'form-' + formID ).request( 'remove:extra', 'stripe_token' );
    },

    /**
     * Remove all stripe errors from our form and fields.
     * @since  3.0
     * @param  {Backbone.model}     fieldModel      Field that has a changed visibility.
     * @return void
     */
    removeAllErrors: function( fieldModel ) {
        var formID = fieldModel.get( 'formID' );
        Backbone.Radio.channel( 'form-' + formID ).request( 'remove:error', 'stripe' );
        if ( 'undefined' != typeof this.processingController ) {
            this.processingController.removeStripeFieldErrors();
        }
    },

    /**
     * Handles the token creation response from both traditional and checkout.
     * @since  3.0.6
     * @param  {object}             token       Response token
     * @param  {Backbone.model}     formModel   form that the token was created for
     * @return void
     */
    tokenSuccess: function( token, formModel ) {
        token.livemode = ( token.livemode ) ? 1 : 0;
        // Set Stripe Extra Data
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'add:extra', 'stripe_token', token.id         );
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'add:extra', 'stripe_live',  token.livemode   );
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'add:extra', 'stripe_last4', token.card.last4 );
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'add:extra', 'stripe_brand', token.card.brand );
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'add:extra', 'stripe_email', token.email );

        // Restart Submission
        nfRadio.channel( 'form-' + formModel.get( 'id' ) ).request( 'submit', formModel );

        return true;
    }

});

/*
 * Traditional Stripe Processor
 */
var nfStripeProcessingController = Marionette.Object.extend({

    errors: [],

    /**
     * When we init, we've been passed a formModel, so set this.formModel and request our stripe token.
     * @since  3.0
     * @param  {Backbone.model}         formModel
     * @param  {Backbone.object}        parent          Controller that is creating and storing this object.
     * @return void
     */
    initialize: function( formModel, parent ) {
        this.parent = parent;
        this.formModel = formModel;

        var that = this;
        Stripe.createToken( this.getOptions() , function( status, response ) {
            that.response( status, response);
        } );

        Backbone.Radio.channel( 'stripe' ).reply( 'remove:errors', this.removeStripeFieldErrors );
    },

    response: function( status, response ) {
        /*
         * Check to see if we have any errors. If we don't, process our token
         */
        if( 'undefined' == typeof response.error ) {
            this.parent.tokenSuccess( response, this.formModel );
        } else {
            /*
             * We have some kind of card error.
             * Try to track down what those errors are.
             */
            if( 402 != status || 'card_error' != response.error.type ) { // Unknown error.
                if ( 'undefined' == typeof response.error ) {
                    var message = nfStripe.genericError;
                } else {
                    var message = response.error.message;
                }
                nfRadio.channel( 'form-' + this.formModel.get( 'id' ) ).request( 'add:error', 'stripe', message );
            } else { // Card-specific error.
                var param = response.error.param || 'number';
                var field = this.getCreditCardField( 'creditcard' + param );
                /*
                 * Add an error message to the appropriate card field.
                 */
                this.addFieldError( field.get( 'id' ), response.error.message );
            }
            /*
             * Tell our form that we've failed submission so that everything resets.
             */
            nfRadio.channel( 'forms' ).trigger( 'submit:failed', this.formModel );
            nfRadio.channel( 'form-' + this.formModel.get( 'id' ) ).trigger( 'submit:failed', this.formModel );
        }

        
    },

    getCreditCardField: function( fieldType ) {

        if( 'undefined' == typeof fieldType ) fieldType = 'creditcardnumber';
        if( 'creditcardexp_year' == fieldType ) fieldType = 'creditcardexpiration';
        if( 'creditcardexp_month' == fieldType ) fieldType = 'creditcardexpiration';


        var fields = _.filter( this.formModel.get( 'fields' ).models, function( field ) {
            return fieldType == field.get( 'type' );
        } );

        return _.first( fields ) || { get: function(){ return ''; } };
    },

    getOptions: function() {

        var expiration = this.getCreditCardField( 'creditcardexpiration' ).get( 'value' );

        return {
            name:            this.getCreditCardField( 'creditcardfullname' ).get( 'value' ),
            number:          this.getCreditCardField( 'creditcardnumber' ).get( 'value' ),
            cvc:             this.getCreditCardField( 'creditcardcvc' ).get( 'value' ),
            exp_month:       _.first( expiration.split( '/' ) ),
            exp_year:        _.last( expiration.split( '/' ) ),
            address_line1:   '',
            address_line2:   '',
            address_city:    '',
            address_state:   '',
            address_zip:     this.getCreditCardField( 'creditcardzip' ).get( 'value' ),
            address_country: ''
        }
    },

    addFieldError: function( fieldID, errorMessage ) {
        this.errors.push({
            fieldID: fieldID,
            errorMessage: errorMessage
        });
        nfRadio.channel( 'fields' ).request( 'add:error', fieldID, 'stripe', errorMessage );
    },

    removeStripeFieldErrors: function() {
        _.each( this.errors, function( error ) {
            nfRadio.channel( 'fields' ).request( 'remove:error', error.fieldID, 'stripe' );
        } );
    }

});

jQuery( document ).ready( function( $ ) {
    new nfStripeController();
});
