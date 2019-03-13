<?php
/**
 * Stripe - Apple Pay Registration Process
 *
 * @package   Give
 * @copyright Copyright (c) 2016, WordImpress
 * @license   https://opensource.org/licenses/gpl-license GNU Public License
 * @since     2.0.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Apple_Pay_Registration
 *
 * @since 2.0.8
 */
class Give_Stripe_Apple_Pay_Registration {

	/**
	 * Give_Stripe_Apple_Pay_Registration constructor.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function __construct() {

		// Bailout, if not stripe gateway activated.
		if ( ! give_is_gateway_active( 'stripe' ) && give_is_setting_enabled( give_get_option( 'stripe_enable_apple_google_pay' ) ) ) {
			return;
		}

		$this->init_apple_pay();
	}

	/**
	 * Initializes Apple Pay process on settings page.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function init_apple_pay() {

		if ( is_admin() ) {
			add_action( 'give_register_stripe_apple_pay_domain', array( $this, 'process_apple_pay_verification' ), 1 );
			add_action( 'give_reset_stripe_apple_pay_domain', array( $this, 'reset_stripe_apple_pay_domain' ), 1 );
		}
	}

	/**
	 * Processes the Apple Pay domain verification.
	 *
	 * @since  2.0.8
	 * @access public
	 */
	public function process_apple_pay_verification() {

		$path        = ABSPATH;
		$folder_name = '.well-known';
		$file_name   = 'apple-developer-merchantid-domain-association';
		$folder_path = "{$path}/{$folder_name}";
		$file_path   = "{$folder_path}/{$file_name}";

		if ( ! file_exists( $folder_path ) ) {
			if ( ! @mkdir( $folder_path, 0755 ) ) { // @codingStandardsIgnoreLine
				give_stripe_record_log(
					__( 'Stripe - Apple Pay Registration Error', 'give-stripe' ),
					__( 'Unable to create domain association folder to domain root.', 'give-stripe' )
				);
			}

			// Log Folder Creation.
			give_stripe_record_log(
				__( 'Apple Pay Registration - Success', 'give-stripe' ),
				__( 'Folder .well-known created successfully.', 'give-stripe' )
			);
		}

		if ( ! file_exists( $file_path ) ) {
			if ( ! @copy( GIVE_STRIPE_PLUGIN_DIR . '/' . $file_name, $file_path ) ) { // @codingStandardsIgnoreLine
				give_stripe_record_log(
					__( 'Apple Pay Registration - Error', 'give-stripe' ),
					__( 'Unable to copy domain association file to domain root.', 'give-stripe' )
				);
			}

			// Log File Moving Process.
			give_stripe_record_log(
				__( 'Apple Pay Registration - Success', 'give-stripe' ),
				__( 'Domain association file successfully copied to root under .well-known folder.', 'give-stripe' )
			);
		}

		// At this point then the domain association folder and file should be available.
		// Proceed to verify/and or verify again.
		$this->register_apple_pay_domain();

	}

	/**
	 * Registers the domain with Stripe/Apple Pay
	 *
	 * @since  2.0.8
	 * @access private
	 */
	private function register_apple_pay_domain() {

		$data = array(
			'domain_name' => $_SERVER['HTTP_HOST'], // @codingStandardsIgnoreLine
		);

		$redirect_to = add_query_arg( array(
			'page'    => 'give-settings',
			'tab'     => 'gateways',
			'section' => 'stripe-settings',
		), admin_url( '/edit.php?post_type=give_forms' ) );

		try {

			// Set Live Secret Key to register domain to Apple Pay.
			\Stripe\Stripe::setApiKey( give_get_option( 'live_secret_key' ) );

			// Domain registration should be processed with LIVE secret keys.
			$response = \Stripe\ApplePayDomain::create( $data, give_stripe_get_connected_account_options() );

			// Log Response.
			give_stripe_record_log(
				__( 'Apple Pay Registration - Success', 'give-stripe' ),
				sprintf(
					/* translators: %s Response. */
					__( 'Received successful response from Stripe. Details %s', 'give-stripe' ),
					$response
				)
			);

			// Set flag in options table to ensure that apple pay domain association is successful.
			give_update_option( 'is_stripe_apple_pay_registered', true );

			// Save Response to options table for future reference.
			give_update_option( 'stripe_apple_pay_response', $response );

			$redirect_to = add_query_arg( 'give-messages[]', 'apple-pay-registration-success', $redirect_to );

		} catch ( Exception $e ) {

			give_update_option( 'is_stripe_apple_pay_registered', false );

			// Record Exception Error in Stripe Logs.
			give_stripe_record_log(
				__( 'Apple Pay Registration - Error', 'give-stripe' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'Unable to register domain association with Apple Pay. Details: %s', 'give-stripe' ),
					$e->getMessage()
				)
			);

			$redirect_to = add_query_arg( 'give-messages[]', 'apple-pay-registration-error', $redirect_to );

		} // End try().

		wp_safe_redirect( $redirect_to );
	}

	/**
	 * Reset Stripe Apple Pay Domain Registration.
	 *
	 * @since 2.1.0
	 */
	public function reset_stripe_apple_pay_domain() {

		$redirect_to = add_query_arg( array(
			'page'    => 'give-settings',
			'tab'     => 'gateways',
			'section' => 'stripe-settings',
		), admin_url( '/edit.php?post_type=give_forms' ) );

		// Reset the apple pay domain registration flag.
		give_update_option( 'is_stripe_apple_pay_registered', false );
		give_delete_option( 'stripe_apple_pay_response' );

		// Record Exception Error in Stripe Logs.
		give_stripe_record_log(
			__( 'Reset Domain Registration', 'give-stripe' ),
			__( 'We\'ve successfully reset the domain registration for Apple Pay', 'give-stripe' )
		);

		$redirect_to = add_query_arg( 'give-messages[]', 'apple-pay-registration-error', $redirect_to );

		wp_safe_redirect( $redirect_to );
	}
}

new Give_Stripe_Apple_Pay_Registration();
