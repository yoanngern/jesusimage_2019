<?php
/**
 * Stripe Admin Functions
 *
 * @package     Give
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since  1.0
 *
 * @param string $transaction_id The Transaction ID.
 * @param int    $payment_id The payment ID for this transaction.
 *
 * @return string                 A link to the Transaction details
 */
function give_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$url = give_stripe_get_transaction_link( $payment_id, $transaction_id );

	return apply_filters( 'give_stripe_link_donation_details_transaction_id', $url );

}

add_filter( 'give_payment_details_transaction_id-stripe', 'give_stripe_link_transaction_id', 10, 2 );
add_filter( 'give_payment_details_transaction_id-stripe_ach', 'give_stripe_link_transaction_id', 10, 2 );


/**
 * Outputs the Stripe Customer ID on the donor profile if found.
 *
 * @since 1.6
 *
 * @param Give_Donor $donor
 */
function give_stripe_output_customer_id_on_donor_profile( $donor ) {

	$customer_id = give_get_stripe_customer_id( $donor->email );
	$test_mode   = give_is_test_mode() ? 'test/' : '';
	$url         = "https://dashboard.stripe.com/{$test_mode}customers/{$customer_id}";
	?>

	<div id="give-stripe-customer-id-wrap" class="donor-section clear">
		<div class="give-stripe-customer-id-inner postbox" style="padding:20px;">
			<form class="give-stripe-update-customer-id" method="post"
					action="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) ); ?>">
				<span class="stripe-customer-id-label"><?php esc_html_e( 'Stripe Customer ID', 'give-stripe' ) ?>:</span>

				<?php if ( ! empty( $customer_id ) ) : ?>
					<a href="<?php echo esc_url( $url ); ?>"
					   target="_blank"
					   class="give-stripe-customer-link"><?php echo $customer_id; ?></a>
				<?php else : ?>
					<span class="give-stripe-customer-link"><?php _e( 'None found', 'give-stripe' ) ?>
						<span class="give-tooltip give-icon give-icon-question"
							  data-tooltip="<?php esc_attr_e( 'This donor does not have a Stripe Customer ID. They likely made their donation(s) using another gateway. You can attach this donor to an existing Stripe Customer by updating this field.', 'give-stripe' ); ?>"></span>
					</span>
				<?php endif; ?>
				<input type="text" class="give-stripe-customer-id-input" name="give_stripe_customer_id"
					   value="<?php echo $customer_id; ?>"/>

				<a href="#"
				   class="button button-small give-stripe-customer-id-update"><?php esc_html_e( 'Update', 'give-stripe' ); ?></a>

				<span class="give-stripe-customer-submit-wrap">
						<button type="submit"
								class="button button-small give-stripe-customer-id-submit"><?php esc_html_e( 'Submit', 'give-stripe' ); ?></button>
						<a href="#"
						   class="button button-small give-stripe-customer-id-cancel"><?php esc_html_e( 'Cancel', 'give-stripe' ); ?></a>
					</span>

				<input type="hidden" name="donor_id" value="<?php echo $donor->id; ?>"/>
				<?php wp_nonce_field( 'edit-donor-stripe-customer-id', '_wpnonce', false, true ); ?>
				<input type="hidden" name="give_action" value="edit_stripe_customer_id"/>
			</form>

		</div>
	</div>
	<?php
}


add_action( 'give_donor_before_address', 'give_stripe_output_customer_id_on_donor_profile', 10, 1 );

/**
 * Updates the Stripe customer ID within the Give DB.
 *
 * @since 1.6
 *
 * @param $args
 *
 * @return bool
 */
function give_stripe_process_customer_id_update( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );


	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'give-stripe' ), __( 'Error', 'give-stripe' ), array(
			'response' => 403,
		) );
	}

	if ( empty( $args ) ) {
		return false;
	}

	$nonce = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor-stripe-customer-id' ) ) {
		wp_die( __( 'WP nonce verification failed.', 'give-stripe' ), __( 'Error', 'give-stripe' ), array(
			'response' => 400,
		) );
	}

	// Sanitize $_POST.
	$posted = give_clean( $_POST ); // WPCS: input var ok.

	$donor_id           = isset( $posted['donor_id'] ) ? $posted['donor_id'] : '';
	$stripe_customer_id = isset( $posted['give_stripe_customer_id'] ) ? $posted['give_stripe_customer_id'] : '';

	// Get the Give donor.
	$donor = new Give_Donor( $donor_id );

	// Update donor meta.
	$donor->update_meta( give_stripe_get_customer_key(), $stripe_customer_id );


}

add_action( 'give_edit_stripe_customer_id', 'give_stripe_process_customer_id_update', 10, 1 );


/**
 * This function will check that the Give test mode and the plaid api endpoint are in sync or not.
 *
 * @since 2.0
 */
function give_stripe_ach_api_endpoint_sync_notice() {

	// Proceed if Stripe + Plaid ACH Settings.
	if ( give_is_gateway_active( 'stripe_ach' ) ) {

		$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		$is_test_mode       = ! empty( $post_data['test_mode'] ) ? give_is_setting_enabled( $post_data['test_mode'] ) : give_is_test_mode();
		$is_plaid_api_mode  = ! empty( $post_data['plaid_api_mode'] ) ? $post_data['plaid_api_mode'] : give_stripe_ach_get_api_endpoint();
		$plaid_settings_url = esc_url( admin_url() . 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-ach-settings' );

		if (
			$is_test_mode
			&& ( 'production' === $is_plaid_api_mode || 'development' === $is_plaid_api_mode )
		) {

			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-incorrect-sync-api-mode',
				'type'        => 'warning',
				'description' => sprintf(
					/* translators: %s Plaid Settings URL */
					__( '<strong>Notice:</strong> You currently are using Give in test mode but have Plaid\'s API in development/live mode. <a href="%1$s">Click here</a> to change the Plaid API mode.' , 'give-stripe' ),
					$plaid_settings_url
				),
			) );

		} elseif (
			! $is_test_mode
			&& 'sandbox' === $is_plaid_api_mode
		) {

			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-incorrect-sync-api-mode',
				'type'        => 'warning',
				'description' => sprintf(
					/* translators: %s Plaid Settings URL */
					__( '<strong>Notice:</strong> You currently are using Give in live mode but have Plaid\'s API in test mode. <a href="%1$s">Click here</a> to change the Plaid API mode.' , 'give-stripe' ),
					$plaid_settings_url
				),
			) );

		}
	} // End if().

}

add_action( 'admin_notices', 'give_stripe_ach_api_endpoint_sync_notice' );

/**
 * Display Recurring Add-on Update Notice.
 *
 * @since 2.0.6
 */
function give_stripe_display_minimum_recurring_version_notice() {

	if (
		defined( 'GIVE_RECURRING_PLUGIN_BASENAME' ) &&
		is_plugin_active( GIVE_RECURRING_PLUGIN_BASENAME )
	) {

		if (
			version_compare( GIVE_STRIPE_VERSION, '2.0.6', '>=' ) &&
			version_compare( GIVE_STRIPE_VERSION, '2.1', '<' ) &&
			version_compare( GIVE_RECURRING_VERSION, '1.7', '<' )
		) {
			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-require-minimum-recurring-version',
				'type'        => 'error',
				'dismissible' => false,
				'description' => __( 'Please update the <strong>Give Recurring Donations</strong> add-on to version 1.7+ to be compatible with the latest version of the Stripe payment gateway.', 'give-stripe' ),
			) );
		} elseif (
			version_compare( GIVE_STRIPE_VERSION, '2.1', '>=' ) &&
			version_compare( GIVE_RECURRING_VERSION, '1.8', '<' )
		) {
			Give()->notices->register_notice( array(
				'id'          => 'give-stripe-require-minimum-recurring-version',
				'type'        => 'error',
				'dismissible' => false,
				'description' => __( 'Please update the <strong>Give Recurring Donations</strong> add-on to version 1.8+ to be compatible with the latest version of the Stripe payment gateway.', 'give-stripe' ),
			) );
		}
	}
}
add_action( 'admin_notices', 'give_stripe_display_minimum_recurring_version_notice' );

/**
 * This function will be useful to register admin notices.
 *
 * @since 2.0.8
 */
function give_stripe_register_admin_notices() {

	// Bailout.
	if ( ! is_admin() ) {
		return;
	}

	$get_data = give_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Bulk action notices.
	if (
		! empty( $get_data['post_type'] ) && 'give_forms' === $get_data['post_type'] &&
		! empty( $get_data['page'] ) && 'give-settings' === $get_data['page'] &&
		! empty( $get_data['tab'] ) && 'gateways' === $get_data['tab'] &&
		! empty( $get_data['section'] ) && 'stripe-settings' === $get_data['section']
	) {

		$message_notices = give_get_admin_messages_key();
		if ( current_user_can( 'manage_options' ) && ! empty( $message_notices ) ) {
			foreach ( $message_notices as $message_notice ) {
				switch ( $message_notice ) {
					case 'apple-pay-registration-error':
						Give()->notices->register_notice( array(
							'id'          => 'give-stripe-apple-pay-error',
							'type'        => 'error',
							'description' => sprintf(
								/* translators: %1$s Stripe Logs URL */
								__( 'An error occurred while registering your site domain with Apple Pay. Please <a href="%1$s">review the error</a> under the Stripe logs.', 'give-stripe' ),
								esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-tools&tab=logs&section=stripe' ) )
							),
							'show'        => true,
						) );
						break;

					case 'apple-pay-registration-success':
						Give()->notices->register_notice( array(
							'id'          => 'give-stripe-apple-pay-success',
							'type'        => 'updated',
							'description' => __( 'You have successfully registered your site domain. You can now begin accepting donations using Apple Pay via Stripe.', 'give-stripe' ),
							'show'        => true,
						) );
						break;
				}
			}
		}
	}
}

add_action( 'admin_notices', 'give_stripe_register_admin_notices', - 1 );
