<?php if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'NF_Abstracts_PaymentGateway' ) ) return;

/**
 * The Stripe payment gateway for the Collect Payment action.
 */
class NF_Stripe_PaymentGateway extends NF_Abstracts_PaymentGateway
{
    protected $_slug = 'stripe';

    protected $forms = array();

    protected $test_secret_key;

    protected $test_publishable_key;

    protected $live_secret_key;

    protected $live_publishable_key;

    public function __construct()
    {
        parent::__construct();

        $this->_name = __( 'Stripe', 'ninja-forms-stripe' );

        add_action( 'init', array( $this, 'init' ) );

        add_action( 'ninja_forms_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        add_action( 'nf_admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
        $api_url = add_query_arg( array(
                                    'page' => 'nf-settings'
                                ), admin_url() . 'admin.php' );
        
        $this->_settings[ 'api_keys' ] = array(
            'name' => 'api_keys',
            'type' => 'html',
            'label' => __( 'HTML', 'ninja-forms'),
            'width' => 'full',
            'group' => 'primary',
            'deps' => array(
                'payment_gateways' => $this->_slug
            ),
            'value' => sprintf( __( 'To edit your API keys, %sclick here%s.', 'ninja-forms-stripe'), '<a href="' . $api_url . '#nf-stripe" target="_blank" >', '</a>' ),
        );

        $this->_settings[ 'stripe_customer_email' ] = array(
            'name'                  => 'stripe_customer_email',
            'type'                  => 'textbox',
            'label'                 => __( 'Customer Email Address', 'ninja-forms-stripe' ),
            'width'                 => 'full',
            'group'                 => 'primary',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug
            ),
            'use_merge_tags'        => TRUE
        );

		$this->_settings[ 'stripe_modal_settings' ] = array(
			'name'                  => 'stripe_modal_settings',
			'type'                  => 'fieldset',
			'label'                 => __( 'Checkout Settings', 'ninja-forms-stripe' ),
			'width'                 => 'full',
			'group'                 => 'advanced',
			'deps'                  => array(
				'payment_gateways'  => $this->_slug,
			),
			'settings'              => array(
				array(
					'name'                  => 'stripe_checkout_logo',
					'type'                  => 'media',
					'label'                 => __( 'Company Logo', 'ninja-forms-stripe' ),
					'width'                 => 'full',
					'group'                 => 'advanced',
					'deps'                  => array(
						'payment_gateways'  => $this->_slug
					)
				),
				array(
					'name'                  => 'stripe_checkout_title',
					'type'                  => 'textbox',
					'label'                 => __( 'Company Name', 'ninja-forms-stripe' ),
					'width'                 => 'full',
					'group'                 => 'advanced',
					'deps'                  => array(
						'payment_gateways'  => $this->_slug
					),
				),
				array(
					'name'                  => 'stripe_checkout_sub_title',
					'type'                  => 'textbox',
					'label'                 => __( 'Company Tagline', 'ninja-forms-stripe' ),
					'width'                 => 'full',
					'group'                 => 'advanced',
					'deps'                  => array(
						'payment_gateways'  => $this->_slug
					)
				),
				array(
					'name'                  => 'stripe_checkout_modal_button_txt',
					'type'                  => 'textbox',
					'label'                 => __( 'Pay Button Text', 'ninja-forms-stripe' ),
					'width'                 => 'full',
					'group'                 => 'advanced',
					'deps'                  => array(
						'payment_gateways'  => $this->_slug
					),
                    'help'                  => sprintf( __( 'Use %s to include the payment total.', 'ninja-forms-stripe' ), '{{amount}}' ),
				)
			),
		);

//	    $this->_settings[ 'stripe_checkout_company_name' ] = array(
//		    'name'                  => 'stripe_checkout_company_name',
//		    'type'                  => 'textbox',
//		    'label'                 => __( 'Checkout Company Name', 'ninja-forms-stripe' ),
//		    'width'                 => 'full',
//		    'group'                 => 'advanced',
//		    'deps'                  => array(
//			    'payment_gateways'  => $this->_slug
//		    )
//	    );

        $this->_settings[ 'stripe_checkout_bitcoin' ] = array(
            'name'                  => 'stripe_checkout_bitcoin',
            'type'                  => 'toggle',
            'label'                 => __( 'Accept Bitcoin With Stripe Checkout', 'ninja-forms-stripe' ),
            'width'                 => 'full',
            'group'                 => 'advanced',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug,
                'stripe_checkout_bitcoin' => true
            ),
        );

	    $this->_settings[ 'stripe_shipping_details'] = array (
		    'name'                  => 'stripe_checkout_shipping_address1',
		    'type'                  => 'fieldset',
		    'label'                 => __( 'Shipping Address Details', 'ninja-forms-stripe' ),
		    'width'                 => 'full',
		    'group'                 => 'advanced',
		    'deps'                  => array(
			    'payment_gateways'  => $this->_slug,
		    ),
		    'settings'              => array(
                array(
                    'name'                  => 'stripe_show_shipping_address_toggle',
                    'type'                  => 'toggle',
                    'label'                 => __( 'Show Shipping Address', 'ninja-forms-stripe' ),
                    'width'                 => 'full',
                    'group'                 => 'advanced',
                    'deps'                  => array(
                        'payment_gateways'  => $this->_slug,
                    ),
                ),
                array(
                    'name'                  => 'stripe_checkout_shipping_name',
                    'type'                  => 'textbox',
                    'label'                 => __( 'Customer Name', 'ninja-forms-stripe' ) . ' <small style="color:red">'
                                            . __( '(required)', 'ninja-forms-stripe' ). '</small>',
                    'width'                 => 'full',
                    'group'                 => 'advanced',
                    'deps'                  => array(
                        'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
                    ),
                    'use_merge_tags'        => TRUE
                ),
			    array(
				    'name'                  => 'stripe_checkout_shipping_address',
				    'type'                  => 'textbox',
				    'label'                 => __( 'Address', 'ninja-forms-stripe' ) . ' <small style="color:red">'
                                            . __( '(required)', 'ninja-forms-stripe' ) . '</small>',
				    'width'                 => 'full',
				    'group'                 => 'advanced',
				    'deps'                  => array(
					    'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
				    ),
				    'use_merge_tags'        => TRUE
			    ),
			    array(
				    'name'                  => 'stripe_checkout_shipping_city',
				    'type'                  => 'textbox',
				    'label'                 => __( 'City', 'ninja-forms-stripe' ),
				    'width'                 => 'full',
				    'group'                 => 'advanced',
				    'deps'                  => array(
					    'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
				    ),
				    'use_merge_tags'        => TRUE
			    ),
			    array(
				    'name'                  => 'stripe_checkout_shipping_state',
				    'type'                  => 'textbox',
				    'label'                 => __( 'State', 'ninja-forms-stripe' ),
				    'width'                 => 'full',
				    'group'                 => 'advanced',
				    'deps'                  => array(
					    'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
				    ),
				    'use_merge_tags'        => TRUE
			    ),
			    array(
				    'name'                  => 'stripe_checkout_shipping_postal_code',
				    'type'                  => 'textbox',
				    'label'                 => __( 'Postal Code', 'ninja-forms-stripe' ),
				    'width'                 => 'full',
				    'group'                 => 'advanced',
				    'deps'                  => array(
					    'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
				    ),
				    'use_merge_tags'        => TRUE
			    ),
			    array(
				    'name'                  => 'stripe_checkout_shipping_country',
				    'type'                  => 'textbox',
				    'label'                 => __( 'Country', 'ninja-forms-stripe' ),
				    'width'                 => 'full',
				    'group'                 => 'advanced',
				    'deps'                  => array(
					    'payment_gateways'  => $this->_slug,
                        'stripe_show_shipping_address_toggle' => 1,
				    ),
				    'use_merge_tags'        => TRUE
			    )
		    )
	    );

//		$this->_settings[ 'stripe_billing_details'] = array (
//			'name'                  => 'stripe_checkout_billing_address1',
//			'type'                  => 'fieldset',
//			'label'                 => __( 'Billing Address Details', 'ninja-forms-stripe' ),
//			'width'                 => 'full',
//			'group'                 => 'advanced',
//			'deps'                  => array(
//				'payment_gateways'  => $this->_slug,
//			),
//			'settings'              => array(
//				array(
//					'name'                  => 'stripe_checkout_billing_address',
//					'type'                  => 'textbox',
//					'label'                 => __( 'Address', 'ninja-forms-stripe' ),
//					'width'                 => 'full',
//					'group'                 => 'advanced',
//					'deps'                  => array(
//						'payment_gateways'  => $this->_slug,
//					),
//					'use_merge_tags'        => TRUE
//				),
//				array(
//					'name'                  => 'stripe_checkout_billing_city',
//					'type'                  => 'textbox',
//					'label'                 => __( 'City', 'ninja-forms-stripe' ),
//					'width'                 => 'full',
//					'group'                 => 'advanced',
//					'deps'                  => array(
//						'payment_gateways'  => $this->_slug,
//					),
//					'use_merge_tags'        => TRUE
//				),
//				array(
//					'name'                  => 'stripe_checkout_billing_state',
//					'type'                  => 'textbox',
//					'label'                 => __( 'State', 'ninja-forms-stripe' ),
//					'width'                 => 'full',
//					'group'                 => 'advanced',
//					'deps'                  => array(
//						'payment_gateways'  => $this->_slug,
//					),
//					'use_merge_tags'        => TRUE
//				),
//				array(
//					'name'                  => 'stripe_checkout_billing_postal_code',
//					'type'                  => 'textbox',
//					'label'                 => __( 'Postal Code', 'ninja-forms-stripe' ),
//					'width'                 => 'full',
//					'group'                 => 'advanced',
//					'deps'                  => array(
//						'payment_gateways'  => $this->_slug,
//					),
//					'use_merge_tags'        => TRUE
//				),
//				array(
//					'name'                  => 'stripe_checkout_billing_country',
//					'type'                  => 'textbox',
//					'label'                 => __( 'Country', 'ninja-forms-stripe' ),
//					'width'                 => 'full',
//					'group'                 => 'advanced',
//					'deps'                  => array(
//						'payment_gateways'  => $this->_slug,
//					),
//					'use_merge_tags'        => TRUE
//				)
//			)
//		);

        $this->_settings[ 'stripe_recurring_plan' ] = array(
            'name'                  => 'stripe_recurring_plan',
            'type'                  => 'textbox',
            'label'                 => __( 'Recurring Payment Plan ID', 'ninja-forms-stripe' ),
            'width'                 => 'full',
            'group'                 => 'advanced',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug,
            ),
            'help'                  => __( 'If you do not want to create a recurring payment, leave this field blank.', 'ninja-forms-stripe' ),
            'use_merge_tags'        => TRUE
        );

        $this->_settings[ 'stripe_test_mode' ] = array(
            'name'                  => 'stripe_test_mode',
            'type'                  => 'toggle',
            'label'                 => __( 'Test Mode', 'ninja-forms' ),
            'width'                 => 'full',
            'group'                 => 'advanced',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug
            ),
            'help'                  => __( 'Use Stripe test credentials to test transaction.', 'ninja-forms-stripe' ),
        );

        $this->_settings[ 'stripe_product_description' ] = array(
            'name'                  => 'stripe_product_description',
            'type'                  => 'textarea',
            'label'                 => __( 'Product Description', 'ninja-forms-stripe' ),
            'width'                 => 'full',
            'group'                 => 'advanced',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug
            ),
            'use_merge_tags'        => TRUE
        );
        $this->_settings[ 'stripe_metadata' ] = array(
            'name' => 'stripe_metadata',
            'type' => 'option-repeater',
            'label' => __( 'Metadata' ) . ' <a href="#" class="nf-add-new">' . __( 'Add New' ) . '</a>',
            'width' => 'full',
            'group' => 'advanced',
            'deps'                  => array(
                'payment_gateways'  => $this->_slug
            ),
            'columns'           => array(
                'key'          => array(
                    'header'    => __( 'Key' ),
                    'default'   => '',
                ),
                'value'          => array(
                    'header'    => __( 'Value' ),
                    'default'   => '',
                ),
            ),
            'tmpl_row'              => 'tmpl-nf-stripe-meta-repeater-row',
            'use_merge_tags'        => TRUE,
            'max_options'           => 20,
        );
        add_action( 'ninja_forms_builder_templates', array( $this, 'nf_stripe_load_templates' ) );
    }

    /**
     * Function to output display templates.
     */
    public function nf_stripe_load_templates() {
        // Template path.
        $file_path = plugin_dir_path( __FILE__ ) . 'Templates' . DIRECTORY_SEPARATOR;
        // Template file list.
        $template_list = array(
            'drawer-settings',
	        'Modal'
        );
        
        foreach( $template_list as $template ){
            if ( file_exists( $file_path . "$template.html.php" ) ) {
	            NF_Stripe()->template( "$template.html.php" );
//                echo file_get_contents( $file_path . "$template.html.php" );
            }
        }

	    ?>
	    <div id="nfStripe"></div>
	    <?php
    }

    /**
     * Process
     *
     * The main function for processing submission data.
     *
     * @param array $action_settings Action specific settings.
     * @param int $form_id The ID of the submitted form.
     * @param array $data Form submission data.
     * @return array $data Modified submission data.
     */
    public function process( $action_settings, $form_id, $data )
    {
        $plan  = $action_settings[ 'stripe_recurring_plan' ];
        $total = $action_settings[ 'payment_total' ] * 100; // Convert the purchase total into cents.
	    $error_msg = '';

        if( ! $total && ! $plan ) {
            return $data;
        }

        // Check for Stripe Token.
        if( ! $data[ 'extra' ][ 'stripe_token' ] ) {
//            $data[ 'errors' ][ 'form' ][ 'stripe-token' ] = __( 'Invalid Stripe Token.', 'ninja-forms-stripe' );
            return $data;
        }

        // Load the stripe libraries
        if ( ! class_exists( 'Stripe' ) ) {
            require_once( NF_Stripe::$dir . '/lib/Stripe.php' );
        }

        Stripe::setApiKey( $this->get_secret_key( $action_settings[ 'stripe_test_mode' ] ) );

        // Get our customer email from the action.
        $email = $action_settings[ 'stripe_customer_email' ];
        // If we didn't get an email from the action...
        // AND we specified an email in the Checkout modal...
        if ( empty( $email ) && isset( $data[ 'extra' ][ 'stripe_email' ] ) ) {
            // Set that as our email instead.
            $email = $data[ 'extra' ][ 'stripe_email' ];
        }

        $charge_data = array(
            'currency' => $this->get_currency( $data ),
            'description' => $action_settings[ 'stripe_product_description' ],
        );

        // If we have a recipient for shipping...
        if ( isset( $action_settings[ 'stripe_checkout_shipping_name' ] ) &&
            ! empty( $action_settings[ 'stripe_checkout_shipping_name' ] ) ) {
            // Attach the shipping address to the charge.
            $charge_data[ 'shipping' ] = array(
                'name' => $action_settings[ 'stripe_checkout_shipping_name' ],
                'address' => array(
                    'city' => $action_settings[ 'stripe_checkout_shipping_city' ],
                    'country' => $action_settings[ 'stripe_checkout_shipping_country' ],
                    'line1' => $action_settings[ 'stripe_checkout_shipping_address' ],
                    'postal_code' => $action_settings[ 'stripe_checkout_shipping_postal_code' ],
                    'state' => $action_settings[ 'stripe_checkout_shipping_state' ]
                )
            );
        }

        // If we have metadata...
        if( ! empty( $action_settings[ 'stripe_metadata' ] ) ){
            // For each metadata object...
            foreach( $action_settings[ 'stripe_metadata' ] as $meta ){
                // Remove whitespace from the key.
                $meta_key = preg_replace( "/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/",
                                         "_", trim( $meta[ 'key' ] ) );
                // If the key is empty, skip it.
                if( empty( $meta_key ) ) continue;
                // Restrict keys to 40 characters.
                if( strlen($meta_key) > 40 ) {
                    $meta_key = substr( $meta_key, 0, 40 );
                }
                $meta_value = trim( $meta[ 'value' ] );
                // If the value is empty, send a null character.
                if( empty( $meta_value ) ) $meta_value = '-';
                // Restrict values to 500 characters.
                if( strlen( $meta_value ) > 500 ) {
                    $meta_value = substr( $meta_value, 0, 500 );
                }
                // Attach the object to the charge.
                $charge_data[ 'metadata' ][$meta_key] = $meta_value;
            }
        }

        // If we don't have a plan.
        if( ! $plan ) {
            // This is a single time charge.
            $charge_data[ 'amount' ] = $total;
        }

        // If we have an email.
        if( $email ){
            // Setup a customer object.
            $customer = array(
                'card' => $data[ 'extra' ][ 'stripe_token' ],
                'email' => $email
            );
            // If we were handed metadata earlier.
            if ( ! empty( $action_settings[ 'stripe_metadata' ] ) ) {
                // Attach it to the customer.
                $customer[ 'metadata' ] = $charge_data[ 'metadata' ];
            }
            // If we have a subscription.
            if( $plan ) {
                // Assign it to the customer.
                $customer[ 'plan' ] = $plan;
            }
            if ( isset( $charge_data[ 'shipping' ] ) ) {
                $customer[ 'shipping' ] = $charge_data[ 'shipping' ];
            }
            try{
                // Create the customer object.
                $customer = Stripe_Customer::create( $customer );
            } catch ( Stripe_CardError $e ) {
                $body = $e->getJsonBody();
                switch( $body[ 'error' ][ 'code' ] ){
                    case 'card_declined':
                        $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                        break;
                    case 'invalid_number':
                        $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                        break;
                    case 'expired_card':
                        $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                        break;
                    case 'incorrect_zip':
                        $location = $this->get_form_field_by_type( 'creditcardzip', $data );
                        break;
                    case 'incorrect_cvc':
                        $location = $this->get_form_field_by_type( 'creditcardcvc', $data );
                        break;
                    case 'invalid_cvc':
                        $location = $this->get_form_field_by_type( 'creditcardcvc', $data );
                        break;
                    case 'invalid_expiry_month':
                        $location = $this->get_form_field_by_type( 'creditcardexpiration', $data );
                        break;
                    case 'invalid_expiry_year':
                        $location = $this->get_form_field_by_type( 'creditcardexpiration', $data );
                        break;
                    default:
                        $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                        break;
                }
                $data[ 'errors' ][ 'fields' ][ $location ] = array( 'message' => $body[ 'error' ][ 'message' ], 'slug' => 'stripe');
                $this->update_sub_customer_data( $data,
	                "Payment incomplete: ( $location: " . $body[ 'error' ][ 'message' ] . " )" );
                return $data;
            } catch ( Stripe_InvalidRequestError $e ) {
                $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	            $this->update_sub_customer_data( $data,
		            "Payment incomplete: ( ". $e->getMessage() . " )" );
                return $data;
            } catch (Stripe_AuthenticationError $e) {
                $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	            $this->update_sub_customer_data( $data,
		            "Payment incomplete: ( ". $e->getMessage() . " )" );
                return $data;
            } catch (Stripe_ApiConnectionError $e) {
                $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	            $this->update_sub_customer_data( $data,
		            "Payment incomplete: ( ". $e->getMessage() . " )" );
                return $data;
            } catch (Stripe_Error $e) {
                $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	            $this->update_sub_customer_data( $data,
		            "Payment incomplete: ( ". $e->getMessage() . " )" );
                return $data;
            } catch (Exception $e) {
                $data[ 'errors' ][ 'form' ][ 'stripe' ] = __( 'There was a problem creating your payment. Please try again.', 'ninja-forms-stripe' );
	            $this->update_sub_customer_data( $data,
		            "Payment incomplete: ( There was a problem creating your payment. Please try again. )" );
                return $data;
            }

            // Setup our customerID merge tag.
            Ninja_Forms()->merge_tags[ 'stripe' ]->set( 'customerID', $customer->id );

            // Add the customerID to the submission.
	        $cust_id_info = $customer->id;
	        if( '' == $customer->id && 0 < strlen( $error_msg ) ) {
	        	$cust_id_info = $error_msg;
	        }
            $this->update_submission( $this->get_sub_id($data), array(
                'stripe_customer_id' => $cust_id_info
            ));

            // If this is a subscription.
            if( $plan ) {
                // Exit here.
                return $data;
            }

        } // Otherwise... (We were not given an email.)
        else {
            // Prepare the charge.
            $charge_data[ 'card' ] = $data[ 'extra' ][ 'stripe_token' ];
            $charge_data[ 'receipt_email' ] = NULL; // Stripe expects NULL as opposed to an empty string
        }

        // If a customer was created...
        if( $customer ){
            // Tie that customer to the charge.
            $charge_data[ 'customer' ] = $customer->id;
        } // Otherwise... (We didn't get a customer.)
        else {
            // Prepare the charge.
            // TODO: This might be redundant.
            $charge_data[ 'card' ] = $data[ 'extra' ][ 'stripe_token' ];
        }

        try {
            // Create the charge.
            $charge = Stripe_Charge::create( $charge_data );
            // Capture the chargeID.
            $this->update_submission( $this->get_sub_id($data), array(
                'stripe_charge_id' => $charge->id
            ));
            // Setup the chargeID merge tag.
            Ninja_Forms()->merge_tags[ 'stripe' ]->set( 'chargeID', $charge->id );
        } catch ( Stripe_CardError $e ) {
            $body = $e->getJsonBody();
            switch( $body[ 'error' ][ 'code' ] ){
                case 'card_declined':
                    $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                    break;
                case 'invalid_number':
                    $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                    break;
                case 'expired_card':
                    $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                    break;
                case 'incorrect_zip':
                    $location = $this->get_form_field_by_type( 'creditcardzip', $data );
                    break;
                case 'incorrect_cvc':
                    $location = $this->get_form_field_by_type( 'creditcardcvc', $data );
                    break;
                case 'invalid_cvc':
                    $location = $this->get_form_field_by_type( 'creditcardcvc', $data );
                    break;
                case 'invalid_expiry_month':
                    $location = $this->get_form_field_by_type( 'creditcardexpiration', $data );
                    break;
                case 'invalid_expiry_year':
                    $location = $this->get_form_field_by_type( 'creditcardexpiration', $data );
                    break;
                default:
                    $location = $this->get_form_field_by_type( 'creditcardnumber', $data );
                    break;
            }
            $data[ 'errors' ][ 'fields' ][ $location ] = array( 'message' => $body[ 'error' ][ 'message' ], 'slug' => 'stripe');
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( $location: " . $body[ 'error' ][ 'message' ] . " )" );
            return $data;
        } catch ( Stripe_InvalidRequestError $e ) {
            $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( ". $e->getMessage() . " )" );
            return $data;
        } catch (Stripe_AuthenticationError $e) {
            $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( ". $e->getMessage() . " )" );
            return $data;
        } catch (Stripe_ApiConnectionError $e) {
            $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( ". $e->getMessage() . " )" );
            return $data;
        } catch (Stripe_Error $e) {
            $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( ". $e->getMessage() . " )" );
            return $data;
        } catch( Exception $e ){
            $data[ 'errors' ][ 'form' ][ 'stripe' ] = $e->getMessage();
	        $this->update_sub_charge_data( $data,
		        "Payment incomplete: ( ". $e->getMessage() . " )" );
            return $data;
        }

        // If we have the last 4 digits...
        if( isset( $data[ 'extra' ][ 'stripe_last4' ] )){
            // Setup the last4 merge tag.
            Ninja_Forms()->merge_tags[ 'stripe' ]->set( 'last4', $data[ 'extra' ][ 'stripe_last4' ] );
        }

        // If we have the card type...
        if( isset( $data[ 'extra' ][ 'stripe_brand' ] )){
            // Setup the cardtype merge tag.
            Ninja_Forms()->merge_tags[ 'stripe' ]->set( 'cardtype', $data[ 'extra' ][ 'stripe_brand' ] );
        }

        return $data;
    }

    public function init()
    {
        $this->test_secret_key      = Ninja_Forms()->get_setting( 'stripe_test_secret_key' );
        $this->test_publishable_key = Ninja_Forms()->get_setting( 'stripe_test_publishable_key' );

        $this->live_secret_key      = Ninja_Forms()->get_setting( 'stripe_live_secret_key' );
        $this->live_publishable_key = Ninja_Forms()->get_setting( 'stripe_live_publishable_key' );
    }

    public function enqueue_scripts( $data )
    {
        $stripe_actions = $this->get_active_stripe_actions( $data[ 'form_id'
        ] );
        if( empty( $stripe_actions ) ) return;

        wp_enqueue_script( 'stripe', 'https://js.stripe.com/v1/', array( 'jquery' ) );
        wp_enqueue_script( 'nf-stripe', NF_Stripe::$url . 'assets/js/stripe.js', array( 'nf-front-end' ) );

        array_push( $this->forms, array( 'id' => $data[ 'form_id' ], 'actions' => $stripe_actions ) );
        $publishable_key = '';

        $preview_settings = get_user_option( 'nf_form_preview_' . $data[ 'form_id' ] );

        if( $preview_settings ){
            foreach( $preview_settings[ 'actions' ] as $action ){
	            // check for collectpayment and stripe
	            if( ! in_array( $action[ 'settings' ][ 'type' ], array( 'stripe',
		            'collectpayment' ) ) ) {
		            continue;
	            }
                if( $this->_slug != $action[ 'settings' ][ 'payment_gateways' ] ) continue;

                $publishable_key = $this->get_publishable_key( $action[ 'settings' ][ 'stripe_test_mode' ] );
            }
        } else {
            foreach( Ninja_Forms()->form( $data[ 'form_id' ] )->get_actions() as $action ) {
	            // check for collectpayment and stripe
	            if( ! in_array( $action->get_setting( 'type' ), array( 'stripe', 'collectpayment' ) ) ) {
		            continue;
	            }
                if( $this->_slug != $action->get_setting( 'payment_gateways' ) ) continue;

                $publishable_key = $this->get_publishable_key( $action->get_setting( 'stripe_test_mode' ) );
            }
        }

        wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js' );

        wp_localize_script( 'nf-stripe', 'nfStripe',
            array(
                'forms' => $this->forms, // array of forms.
                'publishable_key' => $publishable_key,
                'genericError' => __( 'Unkown Stripe Error. Please try again.', 'ninja-forms-stripe' )
            )
        );
    }
    
    public function enqueue_admin_scripts( $data )
    {
        wp_enqueue_script( 'nf-stripe-metadata', NF_Stripe::$url . 'assets/js/metadata.js', array( 'nf-builder' ) );

        wp_enqueue_script( 'nf-stripe-key-modal', NF_Stripe::$url . 'assets/js/actionListener.js', array( 'nf-builder' ) );

        wp_enqueue_style( 'nf-stripe-admin', NF_Stripe::$url . 'assets/css/stripe-builder.css' );

        wp_localize_script( 'nf-stripe-metadata', 'nfStripe',
            array(
                'creditCardFieldDeprecation' => sprintf( __( 'The method our Credit Card Fields use to send information to Stripe has been deprecated.%sIn order to maintain PCI compliance on your site, we recommend removing any Credit Card Fields from your Forms, which will allow your Forms to submit data to Stripe through the Checkout modal.%sFor more information on these changes and PCI compliance, please visit %s', 'ninja-forms-stripe' ), '<br />', '<br />', '<a href="https://stripe.com/docs/security#validating-pci-compliance" target="_blank">https://stripe.com/docs/security#validating-pci-compliance</a>' )
            )
        );

        wp_localize_script( 'nf-stripe-key-modal', 'nfStripeKeys',
	        array (
	        		'hasKeys' => $this->has_api_keys(),
		            'hasStripeAction' => $this->has_active_stripe_action( $data[ 'form_id' ] ),
		            'keyFormatError' => sprintf( __( 'One or more of your entries are incorrectly formatted.' ) )

	        )
        );
    }

    public function has_api_keys() {
    	if( ( $this->test_secret_key && $this->test_publishable_key ) ||
	        ( $this->live_secret_key && $this-> live_publishable_key ) ) {
    		return TRUE;
	    }

    	return FALSE;
    }

    // Check to see if the form currently has a stripe payment action
    private function has_active_stripe_action( $form_id ) {

    	if( 0 === strlen( $form_id ) ) {
	        if ( ! $_REQUEST[ 'form_id' ]
	             || 0 === strlen( $_REQUEST[ 'form_id' ] )
	             || 'new' === $_REQUEST[ 'form_id' ] ) {
	            return false;
	        } else {
	            $form_id = $_REQUEST[ 'form_id' ];
	        }
	    }


	    $form_actions = Ninja_Forms()->form( $form_id )->get_actions();

	    foreach( $form_actions as $action ){
		    // check for collectpayment and stripe
		    if( ! in_array( $action->get_setting( 'type' ), array( 'stripe', 'collectpayment' ) ) ) {
			    continue;
		    }
		    if( $this->_slug == $action->get_setting( 'payment_gateways' ) ) {
		    	return true;
		    }
	    }

	    return false;
    }

    private function get_active_stripe_actions( $form_id ) {
        $form_actions = Ninja_Forms()->form( $form_id )->get_actions();
        $currency = Ninja_Forms()->form( $form_id )->get()->get_setting( 'currency' );
        $stripe_actions = array();
        foreach( $form_actions as $action ) {
	        // check for collectpayment and stripe
	        if( ! in_array( $action->get_setting( 'type' ), array( 'stripe', 'collectpayment' ) ) ) {
		        continue;
	        }
            if( $this->_slug != $action->get_setting( 'payment_gateways' ) ) continue;

	        /*
	         * There was an issue where inactive stripe action were still
	         * being passed to the front-end and being process based on the
	         * order they came in in the array. This line removes 'inactive'
	         * Stripe actions from being passed to the front end
	         */
	        if( 1 != $action->get_setting( 'active' ) ) continue;

            $this->has_active_stripe_action = true;

            $button_label = $action->get_setting( 'stripe_checkout_modal_button_txt', '' );
            $settings = array( 
                'id'        => $action->get_id(),
                'title'     => $action->get_setting( 'stripe_checkout_title', '' ),
                'sub_title' => $action->get_setting( 'stripe_checkout_sub_title', '' ),
                'bitcoin'   => $action->get_setting( 'stripe_checkout_bitcoin', '' ),
                'label'     => $button_label,
                'email'     => $action->get_setting( 'stripe_customer_email', '' ),
                'logo'      => $action->get_setting( 'stripe_checkout_logo', 'https://stripe.com/img/documentation/checkout/marketplace.png' ),
                'plan'      => $action->get_setting( 'stripe_recurring_plan', '' ),
                'total'     => $action->get_setting( 'payment_total', '' ),
                'currency'  => $currency
            );
            $stripe_actions[] = $settings;
        }
        return $stripe_actions;
    }

    private function get_secret_key( $test_mode = false )
    {
        return ( 1 == $test_mode ) ? $this->test_secret_key : $this->live_secret_key;
    }

    private function get_publishable_key( $test_mode = false )
    {
        return ( 1 == $test_mode ) ? $this->test_publishable_key : $this->live_publishable_key;
    }

    private function get_form_field_by_type( $field_type, $data )
    {
        foreach( $data[ 'fields' ] as $field ){
            if( $field_type == $field[ 'type' ] ) return $field[ 'id' ];
        }

        return false;
    }

    /**
      * This will be called on error when creating customer to update the
	  * submission data to add the error to the customer id
     * */
    private function update_sub_customer_data( $data, $error ) {
	    $this->update_submission( $this->get_sub_id( $data ), array(
		    'stripe_customer_id' => $error
	    ));
    }

	/**
	 * This will be called on error when creating charge to update the
	 * submission data to add the error to the charge id
	 * */
	private function update_sub_charge_data( $data, $error ) {
		$this->update_submission( $this->get_sub_id( $data ), array(
			'stripe_charge_id' => $error
		));
	}

    private function update_submission( $sub_id, $data = array() )
    {
        if( ! $sub_id ) return;

        $sub = Ninja_Forms()->form()->sub( $sub_id )->get();

        foreach( $data as $key => $value ){
            $sub->update_extra_value( $key, $value );
        }

        $sub->save();
    }

    private function get_sub_id( $data )
    {
        if( isset( $data[ 'actions' ][ 'save' ][ 'sub_id' ] ) ){
            return $data[ 'actions' ][ 'save' ][ 'sub_id' ];
        }
        return FALSE;
    }

    private function get_currency( $form_data )
    {
        /**
         * Currency Setting Priority
         *
         * 3. Stripe Currency Setting (deprecated)
         * 2. Ninja Forms Currency Setting
         * 1. Form Currency Setting (default)
         */
        $stripe_currency = Ninja_Forms()->get_setting( 'stripe_currency', 'USD' );
        $plugin_currency = Ninja_Forms()->get_setting( 'currency', $stripe_currency );
        $form_currency   = ( isset( $form_data[ 'settings' ][ 'currency' ] ) && $form_data[ 'settings' ][ 'currency' ] ) ? $form_data[ 'settings' ][ 'currency' ] : $plugin_currency;
        return $form_currency;
    }

} // END CLASS NF_Stripe_PaymentGateway
