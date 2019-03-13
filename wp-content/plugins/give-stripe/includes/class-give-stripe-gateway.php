<?php
/**
 * Give Stripe Gateway
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Gateway.
 */
class Give_Stripe_Gateway {

	/**
	 * Default Gateway ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Set Latest Stripe Version.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @var string
	 */
	public $api_version = '2018-08-23';

	/**
	 * Secret API Key.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private static $secret_key = '';

	/**
	 * Give_Stripe_Gateway constructor.
	 */
	public function __construct() {

		// Bailout, if current gateway is not enabled.
		if ( ! self::is_gateway_enabled() ) {
			return false;
		}

		$this->set_api_key();
		$this->set_api_version();

		add_action( "give_gateway_{$this->id}", array( $this, 'process_payment' ) );

		// Add hidden field for source only if the gateway is not Stripe ACH.
		if ( 'stripe_ach' !== $this->id ) {
			add_action( 'give_donation_form_top', array( $this, 'add_hidden_source_field' ), 10, 2 );
		}

	}

	/**
	 * This function will help to set the latest Stripe API version.
	 *
	 * @since  2.0.6
	 * @access public
	 */
	public function set_api_version() {

		try {

			// Set API Version to latest.
			\Stripe\Stripe::setApiVersion( $this->api_version );

		} catch ( \Stripe\Error\Base $e ) {

			// Log Error.
			$this->log_error( $e );

		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to set Stripe API Version. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			$this->send_back_to_checkout();

		}

	}

	/**
	 * This function will help you to set AI Key and its related errors are shown.
	 *
	 * @since  2.0.6
	 * @access public
	 */
	public function set_api_key() {

		try {

			// Fetch and Set API Key.
			\Stripe\Stripe::setApiKey( self::get_secret_key() );

		} catch ( \Stripe\Error\Base $e ) {

			// Log Error.
			$this->log_error( $e );

		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to set Stripe API Key. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			$this->send_back_to_checkout();

		}

	}

	/**
	 * Get the Stripe secret key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {

		self::$secret_key = trim( give_get_option( 'live_secret_key' ) );

		// Update secret key, if test mode is enabled.
		if ( give_is_test_mode() ) {
			self::$secret_key = trim( give_get_option( 'test_secret_key' ) );
		}

		return self::$secret_key;
	}

	/**
	 * Send back to checkout based on the gateway id.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function send_back_to_checkout() {
		give_send_back_to_checkout( '?payment-mode=' . $this->id );
	}

	/**
	 * Is Pre-approved Enabled?
	 *
	 * @since 1.4
	 *
	 * @return bool
	 */
	function is_preapproved_enabled() {
		return give_is_setting_enabled( give_get_option( 'stripe_preapprove_only' ) );
	}

	/**
	 * Is gateway enabled?
	 *
	 * @since 2.0.6
	 *
	 * @return bool
	 */
	function is_gateway_enabled() {
		return give_is_gateway_active( $this->id );
	}

	/**
	 * Is Stripe Popup Enabled.
	 *
	 * @since 1.4
	 *
	 * @return bool
	 */
	public function is_stripe_popup_enabled() {
		return give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled' ) );
	}

	/**
	 * This function will check whether the Stripe Source exists or not.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @return bool
	 */
	public function is_source_exists() {
		return ( isset( $_POST['give_stripe_source'] ) && ! empty( $_POST['give_stripe_source'] ) ); // WPCS: input var ok, sanitization ok, CSRF ok.
	}

	/**
	 * This function will be used to fetch token details from token id.
	 *
	 * @param string $id   Stripe Token ID.
	 * @param array  $args Additional arguments.
	 *
	 * @since  2.0.7
	 * @access public
	 *
	 * @return \Stripe\Token
	 */
	public function get_token_details( $id, $args = array() ) {

		try {

			$args = wp_parse_args( $args, give_stripe_get_connected_account_options() );

			// Retrieve Token Object.
			return \Stripe\Token::retrieve( $id, $args );

		} catch ( \Stripe\Error\Base $e ) {
			$this->log_error( $e );
		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to retrieve source. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			$this->send_back_to_checkout();

		}
	}

	/**
	 * This function will be used to fetch source details from source id.
	 *
	 * @param string $id Stripe Source ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @return \Stripe\Source
	 */
	public function get_source_details( $id ) {

		try {

			// Retrieve Source Object.
			return \Stripe\Source::retrieve( $id, give_stripe_get_connected_account_options() );

		} catch ( \Stripe\Error\Base $e ) {
			$this->log_error( $e );
		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to retrieve source. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			$this->send_back_to_checkout();

		}
	}

	/**
	 * This function will prepare source based on the parameters provided.
	 *
	 * @param array $args List of arguments \Stripe\Source::create() supports.
	 *
	 * @since  2.0.7
	 * @access public
	 *
	 * @return \Stripe\Source
	 */
	public function prepare_source( $args ) {

		try {

			// Create Source Object.
			return \Stripe\Source::create( $args, give_stripe_get_connected_account_options() );

		} catch ( \Stripe\Error\Base $e ) {
			$this->log_error( $e );
		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to create source. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			$this->send_back_to_checkout();

		}
	}

	/**
	 * This function will add hidden source field.
	 *
	 * @param int   $form_id Donation Form ID.
	 * @param array $args    List of arguments.
	 *
	 * @since  2.0.6
	 * @access public
	 */
	public function add_hidden_source_field( $form_id, $args ) {

		$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;

		echo sprintf(
			'<input id="give-%1$s-source-%2$s" type="hidden" name="give_%1$s_source" value="">',
			esc_attr( $this->id ),
			esc_html( $id_prefix )
		);
		?>

		<?php
	}

	/**
	 * Get Customer's card.
	 *
	 * @param \Stripe\Customer $stripe_customer Stripe Customer Object.
	 * @param string           $id              Source or Token ID.
	 *
	 * @return \Stripe\Source|bool
	 */
	public function get_customer_card( $stripe_customer, $id ) {

		$card_exists = false;
		$all_sources = $stripe_customer->sources->all();

		if ( give_is_stripe_checkout_enabled() ) {
			$card = $this->get_token_details( $id );
		} else {
			$card = $this->get_source_details( $id );
		}

		$source_list = wp_list_pluck( $all_sources->data, 'id' );

		// Check whether the source is already attached to customer or not.
		if ( in_array( $id, $source_list, true ) ) {
			$card_exists = true;
		}

		// Create the card if none found above.
		if ( ! $card_exists ) {
			try {

				// Attach Source to existing Customer.
				$card = $stripe_customer->sources->create( array(
					'source' => $id,
				) );

			} catch ( \Stripe\Error\Base $e ) {

				// Log Error.
				$this->log_error( $e );

			} catch ( Exception $e ) {

				give_send_back_to_checkout( "?payment-mode={$this->id}" );
				give_record_gateway_error(
					__( 'Stripe Card Error', 'give-stripe' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ),
						$e->getMessage()
					)
				);
			}
		}

		// Return Card Details, if exists.
		if ( ! empty( $card->id ) ) {
			return $card;
		} else {

			give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
			give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), __( 'An error occurred retrieving or creating the ', 'give-stripe' ) );
			$this->send_back_to_checkout();

			return false;
		}
	}

	/**
	 * Save Stripe Customer ID.
	 *
	 * @param string $stripe_customer_id Customer ID.
	 * @param int    $payment_id         Payment ID.
	 *
	 * @since 1.4
	 */
	function save_stripe_customer_id( $stripe_customer_id, $payment_id ) {

		// Update customer meta.
		if ( class_exists( 'Give_DB_Donor_Meta' ) ) {

			$donor_id = give_get_payment_donor_id( $payment_id );

			// Get the Give donor.
			$donor = new Give_Donor( $donor_id );

			// Update donor meta.
			$donor->update_meta( give_stripe_get_customer_key(), $stripe_customer_id );

		} elseif ( is_user_logged_in() ) {

			// Support saving to legacy method of user method.
			update_user_meta( get_current_user_id(), give_stripe_get_customer_key(), $stripe_customer_id );

		}

	}

	/**
	 * Log a Stripe Error.
	 *
	 * Logs in the Give db the error and also displays the error message to the donor.
	 *
	 * @param \Stripe\Error\Base|\Stripe\Error\Card $exception    Exception.
	 *
	 * @return bool
	 */
	public function log_error( $exception ) {

		$log_message = __( 'The Stripe payment gateway returned an error while processing the donation.', 'give-stripe' ) . '<br><br>';
		$exception_message = $exception->getMessage();

		// Bad Request of some sort.
		if ( ! empty( $exception_message ) ) {
			$log_message .= sprintf(
				/* translators: %s Exception Message */
				__( 'Message: %s', 'give-stripe' ), $exception_message
			) . '<br><br>';

			$trace_string = $exception->getTraceAsString();

			if ( ! empty( $trace_string ) ) {
				$log_message .= sprintf(
					/* translators: %s Trace String */
					__( 'Code: %s', 'give-stripe' ),
					$trace_string
				);
			}

			give_set_error( 'stripe_request_error', $exception_message );
		} else {
			give_set_error( 'stripe_request_error', __( 'The Stripe API request was invalid, please try again.', 'give-stripe' ) );
		}

		// Log it with DB.
		give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), $log_message );
		give_send_back_to_checkout( "?payment-mode={$this->id}" );

		return false;

	}

	/**
	 * Format currency for Stripe.
	 *
	 * @see https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
	 *
	 * @param float $amount Donation amount.
	 *
	 * @return mixed
	 */
	public function format_amount( $amount ) {
		// Get the donation amount.
		if ( give_stripe_is_zero_decimal_currency() ) {
			return $amount;
		} else {
			return $amount * 100;
		}
	}

	/**
	 * Create Source for Stripe 3D Secure Payments.
	 *
	 * @param int $donation_id Donation ID.
	 * @param int $source_id   Source ID/Object.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool|\Stripe\Source
	 */
	public function create_3d_secure_source( $donation_id, $source_id ) {

		$form_id         = give_get_payment_form_id( $donation_id );
		$customer_id     = give_get_payment_meta( $donation_id, '_give_stripe_customer_id', true );
		$donation_amount = give_donation_amount( $donation_id );

		// Prepare basic source args.
		$source_args = array(
			'amount'               => $this->format_amount( $donation_amount ),
			'currency'             => give_get_currency( $form_id ),
			'type'                 => 'three_d_secure',
			'three_d_secure'       => array(
				'card'     => $source_id,
			),
			'statement_descriptor' => give_get_stripe_statement_descriptor(),
			'redirect'             => array(
				'return_url' => add_query_arg(
					array(
						'give-listener' => 'stripe_three_d_secure',
						'donation_id'   => $donation_id,
					),
					give_get_success_page_uri()
				),
			),
		);

		$source = $this->prepare_source( $source_args );

		// Add donation note for 3D secure source ID.
		if ( ! empty( $source->id ) ) {
			give_insert_payment_note( $donation_id, 'Stripe 3D Secure Source ID: ' . $source->id );
		}

		// Save 3D secure source id to donation.
		give_update_payment_meta( $donation_id, '_give_stripe_3dsecure_source_id', $source->id );

		return $source;

	}

	/**
	 * Is 3D secure payment required?
	 *
	 * @param \Stripe\Source $source_object Stripe Source Object.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool
	 */
	public function is_3d_secure_required( $source_object ) {

		$is_3d_secure_enabled = give_is_setting_enabled( give_get_option( 'stripe_enable_three_d_secure_payments', '' ) );

		if ( $is_3d_secure_enabled ) {
			return apply_filters( 'give_stripe_3d_secure_required', (
				! empty( $source_object->type )
				&& 'card' === $source_object->type
				&& 'required' === $source_object->card->three_d_secure
			), $source_object );
		}

		return false;
	}

	/**
	 * Verify Payment.
	 *
	 * @param int            $payment_id         Payment ID.
	 * @param string         $stripe_customer_id Customer ID.
	 * @param \Stripe\Charge $charge             Stripe Charge Object.
	 */
	function verify_payment( $payment_id, $stripe_customer_id, $charge ) {

		// Sanity checks: verify all vars exist.
		if ( $payment_id && ( ! empty( $stripe_customer_id ) || ! empty( $charge ) ) ) {

			// Preapproved payment? These don't get published, rather set to 'preapproval' status.
			if (
				$this->is_preapproved_enabled()
				&& 'stripe_ach' !== give_get_payment_gateway( $payment_id )
			) {

				give_update_payment_status( $payment_id, 'preapproval' );
				add_post_meta( $payment_id, give_stripe_get_customer_key(), $stripe_customer_id );

				$preapproval = new Give_Stripe_Preapproval();
				$preapproval->send_preapproval_admin_notice( $payment_id );
				$preapproval->send_preapproval_donor_notice( $payment_id );

			} else {

				// @TODO use Stripe's API here to retrieve the invoice then confirm it has been paid.
				// Regular payment, publish it.
				give_update_payment_status( $payment_id, 'publish' );
			}

			// Save Stripe customer id.
			$this->save_stripe_customer_id( $stripe_customer_id, $payment_id );

			// Send them to success page.
			give_send_to_success_page();

		} else {

			give_set_error( 'payment_not_recorded', __( 'Your donation could not be recorded, please contact the site administrator.', 'give-stripe' ) );

			// If errors are present, send the user back to the purchase page so they can be corrected.
			$this->send_back_to_checkout();

		} // End if().
	}

	/**
	 * This function will prepare metadata to send to Stripe.
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @return array
	 */
	public function prepare_metadata( $donation_id = 0 ) {

		if ( ! $donation_id ) {
			return array();
		}

		$form_id = give_get_payment_form_id( $donation_id );
		$email   = give_get_payment_user_email( $donation_id );

		$args = array(
			'Email'            => $email,
			'Donation Post ID' => $donation_id,
		);

		// Add Sequential Metadata.
		$seq_donation_id = give_stripe_get_sequential_id( $donation_id );
		if ( $seq_donation_id ) {
			$args['Sequential ID'] = $seq_donation_id;
		}

		// Add custom FFM fields to Stripe metadata.
		$args = array_merge( $args, give_stripe_get_custom_ffm_fields( $form_id, $donation_id ) );

		// Limit metadata passed to Stripe as maximum of 20 metadata is only allowed.
		if ( count( $args ) > 20 ) {
			$args = array_slice( $args, 0, 19, false );
			$args = array_merge( $args, array(
				'More Details' => esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $donation_id ) ),
			) );
		}

		return $args;
	}

	/**
	 * This function will help to charge with Stripe.
	 *
	 * @param int   $donation_id Donation ID with pending status.
	 * @param array $charge_args List of charge arguments.
	 *
	 * @since  2.0.8
	 * @access public
	 *
	 * @return \Stripe\Charge
	 */
	public function create_charge( $donation_id, $charge_args ) {

		try {

			$charge = \Stripe\Charge::create(
				apply_filters( "give_{$this->id}_create_charge_args", $charge_args ),
				give_stripe_get_connected_account_options()
			);

			// Add note for the charge.
			// Save Stripe's charge ID to the transaction.
			if ( ! empty( $charge ) ) {
				give_insert_payment_note( $donation_id, 'Stripe Charge ID: ' . $charge->id );
				give_set_payment_transaction_id( $donation_id, $charge->id );
			}

			return $charge;

		} catch ( \Stripe\Error\Base $e ) {

			Give_Stripe_Logger::log_error( $e, $this->id );
		} catch ( Exception $e ) {

			give_record_gateway_error(
				__( 'Stripe Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Error Message */
					__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);

			give_set_error( 'stripe_charge_error', __( 'Error processing charge with Stripe. Please try again.', 'give-stripe' ) );
			wp_safe_redirect( give_get_failed_transaction_uri() );

		} // End try().
	}
}

return new Give_Stripe_Gateway();
