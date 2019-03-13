<?php
/**
 * Give_Stripe_IDEAL
 *
 * @see https://stripe.com/docs/sources/ideal
 *
 * @package   Give
 * @copyright Copyright (c) 2016, WordImpress
 * @license   https://opensource.org/licenses/gpl-license GNU Public License
 * @since     1.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_IDEAL
 *
 * @since 1.6
 */
class Give_Stripe_IDEAL extends Give_Stripe_Gateway {

	/**
	 * Override Payment Method ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @var string
	 */
	public $id = 'stripe_ideal';

	/**
	 * Give_Stripe_IDEAL constructor.
	 *
	 * @since  1.6
	 * @access public
	 */
	public function __construct() {

		// Register iDEAL Payment Method for Stripe.
		add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );

		parent::__construct();

		// Display admin notice.
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );

		// Remove CC fields when Stripe iDEAL payment method selected.
		add_action(
			'give_stripe_ideal_cc_form', function () {
				return false;
			}
		);

		// Add iDEAL Errors Support.
		add_action( 'give_donation_form_bottom', array( $this, 'add_ideal_errors' ) );

		// Listen to Stripe iDEAL donation.
		add_action( 'init', array( $this, 'listen_stripe_ideal_payment' ) );

	}

	/**
	 * Is Stripe iDEAL enabled?
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return string
	 */
	public function is_ideal_enabled() {
		return give_is_gateway_active( 'stripe_ideal' );
	}

	/**
	 * Returns all supported currencies for this payment method.
	 *
	 * @param int $form_id Donation Form ID.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool
	 */
	public function is_ideal_supported_currency( $form_id ) {
		$supported_currencies = apply_filters(
			'give_stripe_ideal_supported_currencies', array(
				'EUR',
			)
		);

		if ( in_array( give_get_currency( $form_id ), $supported_currencies, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add an errors div
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function add_ideal_errors() {
		echo '<div id="give-stripe-ideal-donation-errors"></div>';
	}

	/**
	 * Register the Stripe payment gateways.
	 *
	 * @param array $gateways List of Payment Gateways.
	 *
	 * @access public
	 * @since  1.6
	 *
	 * @return array
	 */
	public function register_gateway( $gateways ) {

		$gateways['stripe_ideal'] = array(
			'admin_label'    => __( 'Stripe iDEAL', 'give-stripe' ),
			'checkout_label' => __( 'iDEAL', 'give-stripe' ),
		);

		return $gateways;
	}

	/**
	 * Listen to Stripe iDEAL Payment.
	 *
	 * @access public
	 * @since  1.6
	 */
	public function listen_stripe_ideal_payment() {

		// Sanitize the parameter received from query string.
		$data = give_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

		// Must be a stripe iDEAL listener to proceed.
		if ( ! isset( $data['give-listener'] ) || 'stripe_ideal' !== $data['give-listener'] ) {
			return;
		}

		$customer_id = ! empty( $data['customer_id'] ) ? $data['customer_id'] : '';
		$donation_id = ! empty( $data['donation_id'] ) ? $data['donation_id'] : '';
		$description = ! empty( $data['description'] ) ? $data['description'] : '';

		// Retrieve Source Object.
		$source = $this->get_source_details( $data['source'] );

		if ( 'chargeable' === $source->status ) {

			$charge_args = array(
				'amount'               => $source->amount,
				'currency'             => $source->currency,
				'customer'             => $customer_id,
				'source'               => $source->id,
				'description'          => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => $source->statement_descriptor,
				'metadata'             => $this->prepare_metadata( $donation_id ),
			);

			/**
			 * If preapproval enabled, only capture the charge.
			 *
			 * @see https://stripe.com/docs/api#create_charge-capture
			 */
			if ( $this->is_preapproved_enabled() ) {
				$charge_args['capture'] = false;
			}

			$charge = $this->create_charge( $donation_id, $charge_args );

			// Verify Payment.
			$this->verify_payment( $donation_id, $customer_id, $charge );
		} else {

			give_update_payment_status( $donation_id, 'failed' );
			give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), sprintf( __( 'The Stripe Gateway returned an error while processing a donation.', 'give-stripe' ) ) );
			wp_safe_redirect( give_get_failed_transaction_uri() );

		} // End if().

		give_die();
	}

	/**
	 * Process iDEAL Payments.
	 *
	 * @param array $donation_data List of donation data.
	 *
	 * @access public
	 * @since  1.6
	 *
	 * @return bool|void
	 */
	public function process_payment( $donation_data ) {

		$payment_id = false;
		$form_id    = intval( $donation_data['post_data']['give-form-id'] );

		// Make sure we don't have any left over errors present.
		give_clear_errors();

		// Generate error if the currency is not supported by iDEAL.
		if ( ! $this->is_ideal_supported_currency( $form_id ) ) {

			// Not Supported Currency by iDEAL.
			give_set_error(
				'not_ideal_supported_currency',
				sprintf(
					/* translators: 1. Current Currency */
					__( '%1$s is not supported currency with iDEAL. Please try with EUR currency.', 'give-stripe' ),
					give_get_currency( $form_id )
				)
			);

			give_record_gateway_error( __( 'Invalid Currency', 'give-stripe' ), __( 'iDEAL payments only support EUR currency. Please check Stripe logs for more information.', 'give-stripe' ) );
			give_send_back_to_checkout( '?payment-mode=stripe_ideal' );

		}

		// Any errors?
		$errors = give_get_errors();
		$charge = false;

		// No errors, proceed.
		if ( ! $errors ) {

			try {

				$price_id         = isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;

				// https://github.com/impress-org/give-stripe/issues/362
				$donation_summary = htmlentities( give_stripe_payment_gateway_donation_summary( $donation_data ), ENT_COMPAT, 'UTF-8' );

				$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;

				// Get the Stripe customer.
				$give_stripe_customer = new Give_Stripe_Customer( $donor_email );
				$stripe_customer      = $give_stripe_customer->customer_data;
				$stripe_customer_id   = $give_stripe_customer->get_id();

				// We have a Stripe customer, charge them.
				if ( $stripe_customer_id ) {

					// Setup the donation arguments.
					$donation_args = array(
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
						'gateway'         => 'stripe_ideal',
					);

					// Record the pending payment in Give.
					$donation_id = give_insert_payment( $donation_args );

					$source_args = array(
						'type'     => 'ideal',
						'amount'   => $this->format_amount( $donation_data['price'] ),
						'currency' => give_get_currency( $form_id ),
						'owner'    => array(
							'name'  => "{$donation_data['user_info']['first_name']} {$donation_data['user_info']['last_name']}",
							'email' => $donation_data['user_info']['email'],
						),
						'ideal'    => array(
							'statement_descriptor' => give_get_stripe_statement_descriptor( $donation_data ),
						),
						'redirect' => array(
							'return_url' => add_query_arg(
								array(
									'give-listener' => 'stripe_ideal',
									'form_id'       => $form_id,
									'customer_id'   => $stripe_customer_id,
									'donation_id'   => $donation_id,
									'description'   => $donation_summary ,
								),
								give_get_success_page_uri()
							),
						),
					);

					try {

						$source = \Stripe\Source::create( $source_args );

						wp_redirect( $source->redirect->url );
						give_die();

					} catch ( \Stripe\Error\Base $e ) {

						$this->log_error( $e );

					} catch ( Exception $e ) {

						// Something went wrong outside of Stripe.
						give_record_gateway_error(
							__( 'Source Creation Error', 'give-stripe' ),
							sprintf(
								/* translators: %s Exception Message */
								__( 'There is an error while create a source for iDEAL. Details: %s', 'give-stripe' ),
								$e->getMessage()
							)
						);
						give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
						give_send_back_to_checkout( '?payment-mode=stripe_ideal' );

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
					give_send_back_to_checkout( '?payment-mode=stripe_ideal' );

				} // End if().
			} catch ( \Stripe\Error\Base $e ) {

				/**
				 * All the Stripe error classes inherit from `Stripe\Error\Base`, and this first catch block match all the exception sub-classes such as `\Stripe\Error\Card`, `\Stripe\Error\API`, etc.
				 *
				 * @see http://stackoverflow.com/questions/17750143/catching-stripe-errors-with-try-catch-php-method Explanation of the above found in this answer
				 */
				$this->log_error( $e );

			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Error', 'give-stripe' ),
					sprintf(
						/* translators: %s Exception Message */
						__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
				give_send_back_to_checkout( '?payment-mode=stripe_ideal' );

			} // End try().
		} else {
			give_send_back_to_checkout( '?payment-mode=stripe_ideal' );
		} // End if().
	}

	/**
	 * Display Admin notice to show not supported currency.
	 *
	 * @since 2.0
	 */
	public function display_admin_notice() {

		if ( current_user_can( 'manage_give_settings' ) ) {

			$stripe_settings  = give_get_option( 'gateways' );
			$plaid_client_id  = give_get_option( 'plaid_client_id' );
			$plaid_public_key = give_get_option( 'plaid_public_key' );
			$plaid_secret_key = give_get_option( 'plaid_secret_key' );

			$is_stripe_gateway_enabled = (
				array_key_exists( 'stripe', $stripe_settings )
				|| array_key_exists( 'stripe_ach', $stripe_settings )
				|| array_key_exists( 'stripe_ideal', $stripe_settings )
			);


			// Display notice if base currency is not set to Euro or Currency Switcher is not enabled.
			if (
				'EUR' !== give_get_currency() &&
				array_key_exists( 'stripe_ideal', $stripe_settings )
			) {
				Give()->notices->register_notice(
					array(
						'id'          => 'give-stripe-not-supported-currency-notice',
						'type'        => 'error',
						'dismissible' => false,
						'description' => sprintf(
							/* translators: %s Currency Settings Admin URL */
							__( 'The currency must be set as "Euro" within Give\'s <a href="%s">Currency Settings</a> in order to use the Stripe iDEAL payment gateway.', 'give-stripe' ),
							admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=currency-settings' )
						),
						'show'        => true,
					)
				);
			}

			if ( array_key_exists( 'stripe_ach', $stripe_settings )
				&& ( empty( $plaid_client_id )
					|| empty( $plaid_public_key )
					|| empty( $plaid_secret_key )
			) ) {
				Give()->notices->register_notice(
					array(
						'id'          => 'give-plaid-empty-api-key-notice',
						'type'        => 'error',
						'dismissible' => false,
						'description' => sprintf(
							/* translators: %s Stripe Settings Admin URL */
							__( 'The Plaid API Keys should not be empty in <a href="%s">Stripe + Plaid Settings</a> in order to use the Stripe + Plaid payment gateway.', 'give-stripe' ),
							admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-ach-settings' )
						),
						'show'        => true,
					)
				);
			} // End if().
		} // End if().
	}
}

new Give_Stripe_IDEAL();
