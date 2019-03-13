<?php
/**
 * Give_Stripe_ACH
 *
 * Plaid sandbox testing creds: https://blog.plaid.com/plaid-link/
 * username: plaid_test
 * password: plaid_good
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_ACH
 */
class Give_Stripe_ACH extends Give_Stripe_Gateway {

	/**
	 * Override Payment Method ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @var string
	 */
	public $id = 'stripe_ach';

	/**
	 * Array of API keys.
	 *
	 * @var array
	 */
	private $keys = array();

	/**
	 * Give_Stripe_ACH constructor.
	 */
	public function __construct() {

		parent::__construct();

		// Remove CC fieldset.
		add_action( 'give_stripe_ach_cc_form', function () {
			return false;
		} );

		// Load Stripe ACH scripts only when gateway is active.
		if ( give_is_gateway_active( $this->id ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		}

		add_action( 'give_checkout_error_checks', array( $this, 'validate_fields' ), 10, 1 );

		$this->keys = array(
			'client_id'  => trim( give_get_option( 'plaid_client_id' ) ),
			'secret_key' => trim( give_get_option( 'plaid_secret_key' ) ),
			'public_key' => trim( give_get_option( 'plaid_public_key' ) ),
		);

	}

	/**
	 * ACH Scripts.
	 *
	 * Loads scripts from Plaid.
	 */
	function load_scripts() {

		wp_register_script( 'give-plaid-checkout-js', give_stripe_ach_get_plaid_checkout_url(), array( 'jquery' ), null, true );
		wp_enqueue_script( 'give-plaid-checkout-js' );

		wp_register_script( 'give-stripe-ach-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-ach.js', array(
			'jquery',
		), GIVE_STRIPE_VERSION );
		wp_enqueue_script( 'give-stripe-ach-js' );

		wp_localize_script( 'give-stripe-ach-js', 'give_stripe_ach_vars', array(
			'sitename'          => get_bloginfo( 'name' ),
			'plaid_endpoint'    => give_stripe_ach_get_api_endpoint(),
			'plaid_public_key'  => $this->keys['public_key'],
			'plaid_api_version' => give_stripe_ach_get_current_api_version(),
		) );

	}

	/**
	 * Process ACH Payments
	 *
	 * @param array $donation_data List of donation data.
	 *
	 * @return bool
	 */
	function process_payment( $donation_data ) {

		$posted = $donation_data['post_data'];

		// Sanity check: must have Plaid token.
		if ( ! isset( $posted['give_stripe_ach_token'] ) || empty( $posted['give_stripe_ach_token'] ) ) {

			give_record_gateway_error( esc_html__( 'Missing Stripe Token', 'give-stripe' ), esc_html__( 'The Stripe ACH gateway failed to generate the Plaid token.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		} elseif ( ! isset( $posted['give_stripe_ach_account_id'] ) || empty( $posted['give_stripe_ach_account_id'] ) ) {

			give_record_gateway_error( esc_html__( 'Missing Stripe Token', 'give-stripe' ), esc_html__( 'The Stripe ACH gateway failed to generate the Plaid account ID.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		$request = wp_remote_post( give_stripe_ach_get_endpoint_url( 'exchange' ), array(
			'body' => wp_json_encode( array(
				'client_id'    => $this->keys['client_id'],
				'secret'       => $this->keys['secret_key'],
				'public_token' => $posted['give_stripe_ach_token'],
			) ),
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
			),
		) );

		// Error check.
		if ( is_wp_error( $request ) ) {

			give_record_gateway_error(
				esc_html__( 'Missing Stripe Token', 'give-stripe' ),
				sprintf(
					/* translators: %s Error Message */
					__( 'The Stripe ACH gateway failed to make the call to the Plaid server to get the Stripe bank account token along with the Plaid access token that can be used for other Plaid API requests. Details: %s', 'give-stripe' ),
					$request->get_error_message()
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was a problem communicating with the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		// Decode response.
		$response = json_decode( wp_remote_retrieve_body( $request ) );

		// Is there an error returned from the API?
		if ( isset( $response->error_code ) ) {

			give_record_gateway_error(
				esc_html__( 'Plaid API Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Error Message */
					__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
					"{$response->error_code} (error code) - {$response->error_type} (error type) - {$response->error_message}"
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		$request = wp_remote_post( give_stripe_ach_get_endpoint_url( 'bank_account' ), array(
			'body' => wp_json_encode( array(
				'client_id'    => $this->keys['client_id'],
				'secret'       => $this->keys['secret_key'],
				'access_token' => $response->access_token,
				'account_id'   => $posted['give_stripe_ach_account_id'],
			) ),
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
			),
		) );

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		// Is there an error returned from the API?
		if ( isset( $response->error_code ) ) {

			give_record_gateway_error(
				esc_html__( 'Plaid API Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Error Message */
					__( 'An error occurred when processing a donation via Plaid\'s API. Details: %s', 'give-stripe' ),
					"{$response->error_code} (error code) - {$response->error_type} (error type) - {$response->error_message}"
				)
			);
			give_set_error( 'stripe_ach_request_error', esc_html__( 'There was an API error received from the payment gateway. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

			return false;
		}

		// Get Donor Email.
		$donor_email = ! empty( $posted['give_email'] ) ? $posted['give_email'] : 0;

		// Get the Stripe customer.
		$give_stripe_customer = new Give_Stripe_Customer( $donor_email );
		$customer = $give_stripe_customer->customer_data;

		// Check if the bank ID is present for customer in sources.
		$match = $this->check_repeat_donor( $response, $donation_data, $customer );

		// If match, donor is charged.
		if ( $match ) {
			return true;
		}

		// Source doesn't exist for customer, create it, then charge it.
		try {

			// Update Stripe customer with this payment source and charge it.
			$bank_obj = $customer->sources->create( array(
				'source' => $response->stripe_bank_account_token,
			) );

			// Set bank object to array.
			$bank_obj = $bank_obj->__toArray( true );

			$bank_id = isset( $bank_obj['id'] ) ? $bank_obj['id'] : false;

			// Charge the customer.
			$this->charge_ach( $donation_data, $bank_id, $customer->id );

		} catch ( \Stripe\Error\Base $e ) {

			Give_Stripe_Logger::log_error( $e, $this->id );

		} catch ( Exception $e ) {

			give_record_gateway_error(
				esc_html__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					esc_html__( 'The Stripe Gateway returned an error while checking if a Stripe source exists. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		return false;

	}

	/**
	 * Get the Bank ID from Stripe.
	 *
	 * @since 1.4
	 *
	 * @param $response
	 * @param $donation_data
	 * @param $customer
	 *
	 * @return bool
	 */
	function check_repeat_donor( $response, $donation_data, $customer ) {

		$bank_id     = false;
		$fingerprint = false;

		try {

			$token_args = array(
				'expand' => 'id',
			);

			$token_response = $this->get_token_details( $response->stripe_bank_account_token, $token_args );
			$token_response = $token_response->__toArray( true ); // @see http://stackoverflow.com/a/27364648/684352

			$bank_id     = isset( $token_response['bank_account']['id'] ) ? $token_response['bank_account']['id'] : false;
			$fingerprint = isset( $token_response['bank_account']['fingerprint'] ) ? $token_response['bank_account']['fingerprint'] : false;

			// Need a bank ID to continue.
			if ( ! $bank_id ) {

				give_set_error( 'request_error', esc_html__( 'There was a problem identifying your bank account with the payment gateway. Please try you donation again.', 'give-stripe' ) );
				give_send_back_to_checkout( '?payment-mode=stripe_ach' );
				give_record_gateway_error( esc_html__( 'Stripe Error', 'give-stripe' ), esc_html__( 'The Stripe Gateway returned an error while checking if a Stripe source exists.', 'give-stripe' ) );

				return false;
			}
		} catch ( \Stripe\Error\Base $e ) {

			Give_Stripe_Logger::log_error( $e, $this->id );

		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error( esc_html__( 'Stripe Error', 'give-stripe' ), sprintf( esc_html__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ), $e->getMessage() ) );
			give_set_error( 'stripe_error', esc_html__( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ach' );

		}

		$source_args           = array(
			'limit'  => 100,
			'object' => 'bank_account',
		);

		$customer_bank_sources = $customer->sources->all( $source_args )->__toArray( true );
		$match                 = false;

		// Loop through sources and check for match with the new bank ID.
		foreach ( $customer_bank_sources['data'] as $array_key => $bank ) {

			// Bank ID & fingerprint are both viable matching properties.
			if ( $bank['id'] === $bank_id ) {
				$match = true;
			}

			if ( $bank['fingerprint'] === $fingerprint ) {
				$match   = true;
				$bank_id = $bank['id'];
				break;
			}
		}

		// If this bank has already been added to the Stripe customer, charge it now.
		if ( $match ) {

			$this->charge_ach( $donation_data, $bank_id, $customer->id );

			return true; // bounce, the charge has taken place.

		} else {

			// No match found.
			return false;

		}

	}


	/**
	 * Charge ACH.
	 *
	 * @see: http://stackoverflow.com/a/34416413/684352 Useful information on creating a charge using a Stripe bank
	 *       token.
	 *
	 * @param array  $donation_data Donation Data.
	 * @param string $bank_id Bank  Account ID.
	 * @param string $customer_id   Customer ID.
	 */
	function charge_ach( $donation_data, $bank_id, $customer_id ) {

		$form_id     = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
		$price_id    = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
		$description = give_stripe_payment_gateway_donation_summary( $donation_data, false );

		// Setup the payment details.
		$payment_data = array(
			'price'           => $donation_data['price'],
			'give_form_title' => $donation_data['post_data']['give-form-title'],
			'give_form_id'    => $form_id,
			'give_price_id'   => $price_id,
			'date'            => $donation_data['date'],
			'user_email'      => $donation_data['user_email'],
			'purchase_key'    => $donation_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $donation_data['user_info'],
			'status'          => 'pending',
			'gateway'         => 'stripe_ach',
		);

		// Record the pending payment in Give.
		$donation_id = give_insert_payment( $payment_data );

		// Prepare Charge Arguments.
		$charge_args = array(
			'amount'      => parent::format_amount( $donation_data['price'] ),
			'currency'    => give_get_currency(),
			'customer'    => $customer_id,
			'source'      => $bank_id,
			'description' => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
			'metadata'    => $this->prepare_metadata( $donation_id ),
		);

		$charge = $this->create_charge( $donation_id, $charge_args );

		// Verify Stripe ACH Payment.
		parent::verify_payment( $donation_id, $customer_id, $charge );

	}


	/**
	 * Ensure the form.
	 *
	 * @access      public
	 * @since       1.4
	 *
	 * @param $data
	 *
	 * @return      void
	 */
	public function validate_fields( $data ) {

		// Important that we ensure we're only validating this gateway
		if ( isset( $data['gateway'] ) && $data['gateway'] !== 'stripe_ach' ) {
			return;
		}

		// Verify Client ID is there.
		if ( empty( $this->keys['client_id'] ) ) {
			give_set_error( 'give_recurring_stripe_ach_client_id_missing', esc_html__( 'The Plaid client ID must be entered in settings.', 'give-stripe' ) );
			give_record_gateway_error( 'Stripe ACH Error', esc_html__( 'The Plaid client ID must be entered in settings.', 'give-stripe' ) );
		}

		// Verify Secret Key is there.
		if ( empty( $this->keys['secret_key'] ) ) {
			give_set_error( 'give_recurring_stripe_ach_public_missing', esc_html__( 'The Plaid secret key must be entered in settings.', 'give-stripe' ) );
			give_record_gateway_error( 'Stripe ACH Error', esc_html__( 'The Plaid secret key must be entered in settings.', 'give-stripe' ) );
		}

		// Verify Public Key is there.
		if ( empty( $this->keys['public_key'] ) ) {
			give_set_error( 'give_recurring_stripe_ach_public_missing', esc_html__( 'The Plaid public key must be entered in settings.', 'give-stripe' ) );
			give_record_gateway_error( 'Stripe ACH Error', esc_html__( 'The Plaid public key must be entered in settings.', 'give-stripe' ) );
		}

	}


}

new Give_Stripe_ACH();

