<?php
/**
 * Stripe Upgrades.
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
 * Give_Stripe_Upgrades
 *
 * @since  1.2
 */
class Give_Stripe_Upgrades {

	/**
	 * Give_Stripe_Upgrades constructor.
	 */
	public function __construct() {

		$this->version_check();

	}

	/**
	 * Version check.
	 */
	public function version_check() {

		$previous_version = get_option( 'give_stripe_version' );

		// No version option saved.
		if ( version_compare( '1.2', $previous_version, '>' ) || empty( $previous_version ) ) {
			$this->update_v12_preapproval_metakey();
		}
		// Ensure people who update from previous version have billing fields undisturbed.
		if ( version_compare( '1.3', $previous_version, '>' ) ) {
			$this->update_v13_default_billing_fields();
		}

		// Ensure people who update from previous version have billing fields undisturbed.
		if ( version_compare( '1.5', $previous_version, '>' ) ) {
			$this->update_v15_update_options();
		}

		// Update Stripe + Plaid API Key Mode to production instead of live.
		if ( version_compare( '2.0.4', $previous_version, '>' ) ) {
			$this->update_v204_update_api_key_mode_options();
		}

		// Update the version # saved in DB after version checks above.
		update_option( 'give_stripe_version', GIVE_STRIPE_VERSION );

	}


	/**
	 * Update 1.2 Preapproval Metakey.
	 *
	 * Updates a required metakey value due to a typo causing a bug.
	 *
	 * @see        : https://github.com/WordImpress/Give-Stripe/pull/1 and https://github.com/WordImpress/Give-Stripe/pull/2
	 */
	private function update_v12_preapproval_metakey() {

		global $wpdb;
		$sql = "UPDATE $wpdb->postmeta SET `meta_key` = '_give_stripe_customer_id' WHERE `meta_key` LIKE '_give_stripe_stripe_customer_id'";
		$wpdb->query( $sql );

	}

	/**
	 * Update 1.3 Collect Billing Details
	 *
	 * Sets the default option to display Billing Details as to not mess with any donation forms without consent
	 *
	 * @see https://github.com/WordImpress/Give-Stripe/issues/11
	 */
	private function update_v13_default_billing_fields() {

		give_update_option( 'stripe_collect_billing', 'on' );

	}

	/**
	 * Update 1.5 Update Options
	 *
	 * Sets the default option to display Billing Details as to not mess with any donation forms without consent
	 *
	 * @see https://github.com/WordImpress/Give-Stripe/issues/11
	 */
	private function update_v15_update_options() {

		give_update_option( 'stripe_checkout_zip_verify', 'on' );
		give_update_option( 'stripe_checkout_remember_me', 'on' );

	}

	/**
	 * Update options for 2.0.4
	 *
	 * Set Plaid API mode for LIVE to PRODUCTION
	 *
	 * @since 2.0.4
	 *
	 * @see https://github.com/WordImpress/Give-Stripe/issues/199
	 */
	private function update_v204_update_api_key_mode_options() {
		$plaid_api_mode = give_get_option( 'plaid_api_mode' );
		if ( 'live' === $plaid_api_mode ) {
			give_update_option( 'plaid_api_mode', 'production' );
		}
	}

}

return new Give_Stripe_Upgrades();
