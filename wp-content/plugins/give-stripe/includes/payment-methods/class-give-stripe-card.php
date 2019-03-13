<?php
/**
 * Give Stripe Card
 *
 * @package   Give
 * @copyright Copyright (c) 2016, WordImpress
 * @license   https://opensource.org/licenses/gpl-license GNU Public License
 * @since     2.0.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Card.
 *
 * @since 2.0.6
 */
class Give_Stripe_Card extends Give_Stripe_Gateway {

	/**
	 * Override Payment Method ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @var string
	 */
	public $id = 'stripe';

	/**
	 * Give_Stripe_Card constructor.
	 *
	 * @since  2.0.6
	 * @access public
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'init', array( $this, 'stripe_event_listener' ) );
		add_action( 'init', array( $this, 'listen_stripe_3dsecure_payment' ) );
		
		add_filter( 'give_require_billing_address', array( $this, 'disable_address_validation_for_payment_request' ) );
		add_filter( 'give_donation_form_required_fields', array( $this, 'remove_default_required_fields' ), 10, 1 );

	}
	
	/**
	 * This function will be used to remove default credit card fields validation.
	 *
	 * @param array $required_fields List of required fields for validation.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return array
	 */
	public function remove_default_required_fields( $required_fields ) {
		
		// Unset card name when stripe is the selected gateway.
		if ( give_is_gateway_active( 'stripe' ) ) {
			
			// Unset the card name field
			unset( $required_fields['card_name'] );
		}
		
		return $required_fields;
	}
	
	/**
	 * This function will be used to disable the address fields validation for payment request (like Apple and Google Pay)
	 *
	 * @param bool $status True or False.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function disable_address_validation_for_payment_request( $status ) {
		
		$posted_data                = filter_input_array( INPUT_POST );
		$is_billing_enabled         = give_is_setting_enabled( give_get_option( 'stripe_collect_billing' ) );
		$is_stripe_checkout_enabled = give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled' ) );

		// Bailout, if payment request tab data is not submitted.
		if (
			! empty( $posted_data['give-form-id'] ) &&
			give_is_gateway_active( 'stripe' ) &&
			'stripe' === give_get_chosen_gateway( $posted_data['give-form-id'] ) &&
			! empty( $posted_data['is_payment_request'] ) &&
			$is_billing_enabled &&
			! $is_stripe_checkout_enabled
		) {
			return true;
		}
		
		return false;
	}

	/**
	 * This function will be used to validate CC fields.
	 *
	 * @param array $post_data List of posted variables.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @return void
	 */
	public function validate_fields( $post_data ) {

		if (
			! give_is_stripe_checkout_enabled() &&
			'single' !== give_get_option( 'stripe_cc_fields_format', 'multi' ) &&
			! isset( $post_data['post_data']['is_payment_request'] ) &&
			isset( $post_data['card_info']['card_name'] ) &&
			empty( $post_data['card_info']['card_name'] )
		) {
			give_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'give-stripe' ) );
		}

	}

	/**
	 * Process the POST Data for the Credit Card Form, if a source was not supplied.
	 *
	 * @since 2.0.6
	 *
	 * @param array $donation_data List of donation data.
	 *
	 * @return array The credit card data from the $_POST
	 */
	public function prepare_card_data( $donation_data ) {

		$card_data = array(
			'number'          => $donation_data['card_info']['card_number'],
			'name'            => $donation_data['card_info']['card_name'],
			'exp_month'       => $donation_data['card_info']['card_exp_month'],
			'exp_year'        => $donation_data['card_info']['card_exp_year'],
			'cvc'             => $donation_data['card_info']['card_cvc'],
			'address_line1'   => $donation_data['card_info']['card_address'],
			'address_line2'   => $donation_data['card_info']['card_address_2'],
			'address_city'    => $donation_data['card_info']['card_city'],
			'address_zip'     => $donation_data['card_info']['card_zip'],
			'address_state'   => $donation_data['card_info']['card_state'],
			'address_country' => $donation_data['card_info']['card_country'],
		);

		return $card_data;
	}

	/**
	 * Check for the Stripe Source.
	 *
	 * @param array $donation_data List of Donation Data.
	 *
	 * @since 2.0.6
	 *
	 * @return string
	 */
	public function check_for_source( $donation_data ) {

		$source_id          = $donation_data['post_data']['give_stripe_source'];
		$stripe_js_fallback = give_get_option( 'stripe_js_fallback' );

		if ( ! isset( $source_id ) ) {

			// check for fallback mode.
			if ( ! empty( $stripe_js_fallback ) ) {

				$card_data = $this->prepare_card_data( $donation_data );

				try {

					$source = \Stripe\Source::create( array(
						'card' => $card_data,
					) );
					$source_id = $source->id;

				} catch ( \Stripe\Error\Base $e ) {
					$this->log_error( $e );

				} catch ( Exception $e ) {

					give_record_gateway_error(
						__( 'Stripe Error', 'give-stripe' ),
						sprintf(
							/* translators: %s Exception Message Body */
							__( 'The Stripe Gateway returned an error while creating the customer payment source. Details: %s', 'give-stripe' ),
							$e->getMessage()
						)
					);
					give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give-stripe' ) );
					give_send_back_to_checkout( "?payment-mode={$this->id}&form_id={$donation_data['post_data']['give-form-id']}" );
				}
			} elseif ( ! $this->is_stripe_popup_enabled() ) {

				// No Stripe source and fallback mode is disabled.
				give_set_error( 'no_token', __( 'Missing Stripe Source. Please contact support.', 'give-stripe' ) );
				give_record_gateway_error( __( 'Missing Stripe Source', 'give-stripe' ), __( 'A Stripe token failed to be generated. Please check Stripe logs for more information.', 'give-stripe' ) );

			}
		} // End if().

		return $source_id;

	}

	/**
	 * This function will be used for donation processing.
	 *
	 * @param array $donation_data List of donation data.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function process_payment( $donation_data ) {

		// Bailout, if the current gateway and the posted gateway mismatched.
		if ( $this->id !== $donation_data['post_data']['give-gateway'] ) {
			return;
		}

		// Make sure we don't have any left over errors present.
		give_clear_errors();

		// Validate CC Fields.
		$this->validate_fields( $donation_data );

		$source_id = ! empty( $donation_data['post_data']['give_stripe_source'] )
			? $donation_data['post_data']['give_stripe_source']
			: $this->check_for_source( $donation_data );

		// Any errors?
		$errors = give_get_errors();

		// No errors, proceed.
		if ( ! $errors ) {

			$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
			$price_id         = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
			$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;
			$donation_summary = give_stripe_payment_gateway_donation_summary( $donation_data, false );

			// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
			$give_stripe_customer = new Give_Stripe_Customer( $donor_email, $source_id );
			$stripe_customer      = $give_stripe_customer->customer_data;
			$stripe_customer_id   = $give_stripe_customer->get_id();

			// We have a Stripe customer, charge them.
			if ( $stripe_customer_id ) {

				// Proceed to get stripe source details on if stripe checkout is not enabled.
				$source    = $give_stripe_customer->attached_source;
				$source_id = $source->id;

				// Setup the payment details.
				$payment_data = array(
					'price'           => $donation_data['price'],
					'give_form_title' => $donation_data['post_data']['give-form-title'],
					'give_form_id'    => $form_id,
					'give_price_id'   => $price_id,
					'date'            => $donation_data['date'],
					'user_email'      => $donation_data['user_email'],
					'purchase_key'    => $donation_data['purchase_key'],
					'currency'        => give_get_currency( $form_id ),
					'user_info'       => $donation_data['user_info'],
					'status'          => 'pending',
					'gateway'         => $this->id,
				);

				// Record the pending payment in Give.
				$donation_id = give_insert_payment( $payment_data );

				// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
				give_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
				$this->save_stripe_customer_id( $stripe_customer_id, $donation_id );
				give_update_meta( $donation_id, '_give_stripe_customer_id', $stripe_customer_id );

				// Add donation note for source ID.
				give_insert_payment_note( $donation_id, 'Stripe Source ID: ' . $source_id );

				// Save source id to donation.
				give_update_meta( $donation_id, '_give_stripe_source_id', $source_id );

				// Save donation summary to donation.
				give_update_meta( $donation_id, '_give_stripe_donation_summary', $donation_summary );

				// Assign required data to array of donation data for future reference.
				$donation_data['donation_id'] = $donation_id;
				$donation_data['customer_id'] = $stripe_customer_id;
				$donation_data['description'] = $donation_summary;
				$donation_data['source_id']   = $source_id;

				if ( ! give_is_stripe_checkout_enabled() && $this->is_3d_secure_required( $source ) ) {

					// Create a 3D secure source object.
					$source_object = $this->create_3d_secure_source( $donation_id, $source_id );

					// Redirect to authorise payment after receiving 3D Secure Source Response.
					wp_redirect( esc_url_raw( $source_object->redirect->url ) );
					give_die();

				} else {

					// Process charge w/ support for preapproval.
					$charge = $this->process_charge( $donation_data, $stripe_customer_id );

					// Verify the Stripe payment.
					$this->verify_payment( $donation_id, $stripe_customer_id, $charge );

				}
			} else {

				// No customer, failed.
				give_record_gateway_error(
					__( 'Stripe Customer Creation Failed', 'give-stripe' ),
					sprintf(
						/* translators: %s Donation Data */
						__( 'Customer creation failed while processing the donation. Details: %s', 'give-stripe' ),
						wp_json_encode( $donation_data )
					)
				);
				give_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while processing the donation.', 'give-stripe' ) );
				give_send_back_to_checkout( "?payment-mode={$this->id}" );

			} // End if().
		} else {
			give_send_back_to_checkout( "?payment-mode={$this->id}" );
		} // End if().
	}

	/**
	 * Process One Time Charge.
	 *
	 * @param array  $donation_data      List of donation data.
	 * @param string $stripe_customer_id Customer ID.
	 *
	 * @return bool|\Stripe\Charge
	 */
	function process_charge( $donation_data, $stripe_customer_id ) {

		$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
		$donation_id      = ! empty( $donation_data['donation_id'] ) ? intval( $donation_data['donation_id'] ) : 0;

		// Process the charge.
		$amount = $this->format_amount( $donation_data['price'] );

		$charge_args = array(
			'amount'               => $amount,
			'currency'             => give_get_currency( $form_id ),
			'customer'             => $stripe_customer_id,
			'description'          => html_entity_decode( $donation_data['description'], ENT_COMPAT, 'UTF-8' ),
			'statement_descriptor' => give_get_stripe_statement_descriptor( $donation_data ),
			'metadata'             => $this->prepare_metadata( $donation_id ),
		);

		/**
		 * If preapproval enabled, only capture the charge
		 *
		 * @see https://stripe.com/docs/api#create_charge-capture
		 */
		if ( $this->is_preapproved_enabled() ) {
			$charge_args['capture'] = false;
		}

		// Create charge with general gateway fn.
		$charge = $this->create_charge( $donation_id, $charge_args );

		// Return charge if set.
		if ( isset( $charge ) ) {
			return $charge;
		} else {
			return false;
		}
	}

	/**
	 * Listen for Stripe events.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function stripe_event_listener() {

		// Must be a stripe listener to proceed.
		if ( ! isset( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
			return;
		}

		// Get the Stripe SDK autoloader.
		require_once GIVE_STRIPE_PLUGIN_DIR . '/vendor/autoload.php';

		$this->set_api_key();
		$this->set_api_version();

		// Retrieve the request's body and parse it as JSON.
		$body       = @file_get_contents( 'php://input' );
		$event_json = json_decode( $body );

		$this->process_webhooks( $event_json );

	}

	/**
	 * Process Stripe Webhooks.
	 *
	 * @since 1.5
	 *
	 * @param object $event_json Stripe Webhook JSON.
	 */
	public function process_webhooks( $event_json ) {

		// Next, proceed with additional webhooks.
		if ( isset( $event_json->id ) ) {

			status_header( 200 );

			try {

				$event = \Stripe\Event::retrieve( $event_json->id );

			} catch ( \Stripe\Error\Authentication $e ) {

				if ( strpos( $e->getMessage(), 'Platform access may have been revoked' ) !== false ) {
					give_stripe_connect_delete_options();
				}
			} catch ( Exception $e ) {

				die( 'Invalid event ID' );

			}

			switch ( $event->type ) :

				case 'charge.refunded' :

					global $wpdb;

					$charge = $event->data->object;

					if ( $charge->refunded ) {

						$payment_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $charge->id ) );

						if ( $payment_id ) {

							give_update_payment_status( $payment_id, 'refunded' );
							give_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe.', 'give-stripe' ) );

						}
					}

					break;

			endswitch;

			do_action( 'give_stripe_event_' . $event->type, $event );

			die( '1' ); // Completed successfully.

		} else {
			status_header( 500 );
			// Something went wrong outside of Stripe.
			give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), sprintf( __( 'An error occurred while processing a webhook.', 'give-stripe' ) ) );
			die( '-1' ); // Failed.
		} // End if().
	}

	/**
	 * Authorise Donation to successfully complete the donation.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return void
	 */
	public function listen_stripe_3dsecure_payment() {

		// Sanitize the parameter received from query string.
		$data = give_clean( $_GET ); // WPCS: input var ok.

		// Must be a stripe three-d-secure listener to proceed.
		if ( ! isset( $data['give-listener'] ) || 'stripe_three_d_secure' !== $data['give-listener'] ) {
			return;
		}

		$donation_id = ! empty( $data['donation_id'] ) ? $data['donation_id'] : '';
		$source_id   = ! empty( $data['source'] ) ? $data['source'] : '';
		$description = give_get_meta( $donation_id, '_give_stripe_donation_summary', true );
		$customer_id = give_get_meta( $donation_id, '_give_stripe_customer_id', true );

		// Get Source Object from source id.
		$source_object = $this->get_source_details( $source_id );

		// Proceed to charge, if the 3D secure source is chargeable.
		if ( 'chargeable' === $source_object->status ) {

			$charge_args = array(
				'amount'               => $source_object->amount,
				'currency'             => $source_object->currency,
				'customer'             => $customer_id,
				'source'               => $source_object->id,
				'description'          => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => $source_object->statement_descriptor,
				'metadata'             => $this->prepare_metadata( $donation_id ),
			);

			// If preapproval enabled, only capture the charge
			// @see: https://stripe.com/docs/api#create_charge-capture.
			if ( $this->is_preapproved_enabled() ) {
				$charge_args['capture'] = false;
			}

			try {

				$charge = $this->create_charge( $donation_id, $charge_args );

				if ( $charge ) {

					/**
					 * This action hook will help to perform additional steps when 3D secure payments are processed.
					 *
					 * @since 2.1
					 *
					 * @param int            $donation_id Donation ID.
					 * @param \Stripe\Charge $charge      Stripe Charge Object.
					 * @param string         $customer_id Stripe Customer ID.
					 */
					do_action( 'give_stripe_verify_3dsecure_payment', $donation_id, $charge, $customer_id );

					// Verify Payment.
					$this->verify_payment( $donation_id, $customer_id, $charge );
				}
			} catch ( \Stripe\Error\Base $e ) {

				$this->log_error( $e );

			} catch ( Exception $e ) {

				give_update_payment_status( $donation_id, 'failed' );
				give_record_gateway_error(
					__( 'Stripe Error', 'give-stripe' ),
					sprintf(
						/* translators: Exception Message Body */
						__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ),
						$e->getMessage()
					)
				);
				wp_safe_redirect( give_get_failed_transaction_uri() );
			} // End try().
		} else {

			give_update_payment_status( $donation_id, 'failed' );
			give_record_gateway_error( __( 'Donor Error', 'give-stripe' ), sprintf( __( 'Donor has cancelled the payment during authorization process.', 'give-stripe' ) ) );
			wp_safe_redirect( give_get_failed_transaction_uri() );

		} // End if().

		give_die();
	}

}

return new Give_Stripe_Card();
