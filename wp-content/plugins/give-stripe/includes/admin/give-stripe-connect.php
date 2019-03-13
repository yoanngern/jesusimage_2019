<?php
/**
 * Give Stripe Gateway Connect
 *
 * @package     Give
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the "Give Connect" banner.
 *
 * @see: https://stripe.com/docs/connect/reference
 *
 * @return bool
 */
function give_stripe_connect_maybe_show_banner() {

	// Don't show if already connected.
	if ( give_is_stripe_connected() ) {
		return false;
	}

	// Don't show if user wants to use their own API key.
	$user_api_keys_enabled = give_is_setting_enabled( give_get_option( 'stripe_user_api_keys' ) );
	if ( $user_api_keys_enabled ) {
		return false;
	}

	// Don't show if on the payment settings section.
	if ( 'stripe-settings' === give_get_current_setting_section() ) {
		return false;
	}

	// Don't show for non-admins.
	if ( ! current_user_can( 'update_plugins' ) ) {
		return false;
	}

	// Is the notice temporarily dismissed?
	if ( give_is_connect_notice_dismissed() ) {
		return false;
	}

	$give_stripe  = Give_Stripe::get_instance();
	$connect_link = give_stripe_connect_button();

	// Default message.
	$main_text = __( 'You\'re almost ready to start accepting online donations. <a href="#" class="give-stripe-connect-temp-dismiss">Not right now <span class="dashicons dashicons-dismiss"></span></a>', 'give-stripe' );

	if ( give_stripe_connect_has_user_added_keys() ) {
		$main_text = __( 'Give has implemented a more secure way to connect with Stripe. <a href="#" class="give-stripe-connect-temp-dismiss">Remind me later <span class="dashicons dashicons-dismiss"></span></a>', 'give-stripe' );
	}

	$message = sprintf(
		/* translators: 1. Main Text, 2. Connect Link */
		__( '<strong>Stripe Connect:</strong> %1$s %2$s', 'give-stripe' ),
		$main_text,
		$connect_link
	);

	$give_stripe->add_admin_notice( 'prompt_connect', 'notice notice-warning give-stripe-connect-message', $message );

	return true;

}

add_action( 'admin_notices', 'give_stripe_connect_maybe_show_banner' );


/**
 * Check if the user has manually added keys.
 */
function give_stripe_connect_has_user_added_keys() {

	$live_secret          = give_get_option( 'live_secret_key' );
	$test_secret          = give_get_option( 'test_secret_key' );
	$live_publishable_key = give_get_option( 'live_publishable_key' );
	$test_publishable_key = give_get_option( 'test_publishable_key' );

	if (
		! empty( $live_secret )
		|| ! empty( $test_secret )
		|| ! empty( $live_publishable_key )
		|| ! empty( $test_publishable_key )
	) {
		return true;
	}

	return false;
}


/**
 * Dismiss connect banner temporarily.
 *
 * Sets transient via AJAX callback.
 */
function give_stripe_connect_dismiss_banner() {

	$user_id = get_current_user_id();
	set_transient( "give_hide_stripe_connect_notice_{$user_id}", '1', DAY_IN_SECONDS );

	return true;

}

add_action( 'give_stripe_connect_dismiss', 'give_stripe_connect_dismiss_banner' );

/**
 * Check if notice dismissed by admin user or not.
 *
 * @since  1.5
 *
 * @return bool
 */
function give_is_connect_notice_dismissed() {

	$current_user        = wp_get_current_user();
	$is_notice_dismissed = false;

	if ( get_transient( "give_hide_stripe_connect_notice_{$current_user->ID}" ) ) {
		$is_notice_dismissed = true;
	}

	return $is_notice_dismissed;
}

/**
 * Stripe Connect Button.
 *
 * @return string
 */
function give_stripe_connect_button() {

	$connected = give_get_option( 'give_stripe_connected' );

	// Prepare Stripe Connect URL.
	$link = add_query_arg(
		array(
			'stripe_action'         => 'connect',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'give_stripe_connected' => ! empty( $connected ) ? '1' : '0',
		),
		'https://connect.givewp.com/stripe/connect.php'
	);

	return apply_filters( 'give_stripe_connect_button', sprintf( '<a href="%s" id="give-stripe-connect"><span>Connect with Stripe</span></a>', esc_url( $link ) ) );
}


/**
 * Once the user returns from connecting, save the options.
 */
function give_stripe_connect_save_options() {

	$get_vars = give_clean( $_GET ); // WPCS: input var ok.

	// If we don't have values here, bounce.
	if (
		! isset( $get_vars['stripe_publishable_key'] )
		|| ! isset( $get_vars['stripe_user_id'] )
		|| ! isset( $get_vars['stripe_access_token'] )
		|| ! isset( $get_vars['stripe_access_token_test'] )
		|| ! isset( $get_vars['connected'] )
	) {
		return false;
	}

	// Update keys.
	give_update_option( 'give_stripe_connected', $get_vars['connected'] );
	give_update_option( 'give_stripe_user_id', $get_vars['stripe_user_id'] );
	give_update_option( 'live_secret_key', $get_vars['stripe_access_token'] );
	give_update_option( 'test_secret_key', $get_vars['stripe_access_token_test'] );
	give_update_option( 'live_publishable_key', $get_vars['stripe_publishable_key'] );
	give_update_option( 'test_publishable_key', $get_vars['stripe_publishable_key_test'] );

	// Delete option for user API key.
	give_delete_option( 'stripe_user_api_keys' );

}

add_action( 'admin_init', 'give_stripe_connect_save_options' );


/**
 * Get Stripe connect options.
 *
 * @return mixed
 */
function get_give_stripe_connect_options() {

	$options = array(
		'connected_status'     => give_get_option( 'give_stripe_connected' ),
		'user_id'              => give_get_option( 'give_stripe_user_id' ),
		'access_token'         => give_get_option( 'live_secret_key' ),
		'access_token_test'    => give_get_option( 'test_secret_key' ),
		'publishable_key'      => give_get_option( 'live_publishable_key' ),
		'publishable_key_test' => give_get_option( 'test_publishable_key' ),
	);

	return apply_filters( 'get_give_stripe_connect_options', $options );
}


/**
 * Conditional to check if Stripe is connected.
 *
 * @return bool
 */
function give_is_stripe_connected() {

	$options = get_give_stripe_connect_options();

	$user_api_keys_enabled = give_is_setting_enabled( give_get_option( 'stripe_user_api_keys' ) );

	if ( $user_api_keys_enabled ) {
		return false;
	}

	// Check all the necessary options.
	if (
		! empty( $options['connected_status'] ) && '1' === $options['connected_status']
		&& ! empty( $options['user_id'] )
		&& ! empty( $options['access_token'] )
		&& ! empty( $options['access_token_test'] )
		&& ! empty( $options['publishable_key'] )
		&& ! empty( $options['publishable_key_test'] )
	) {
		return true;
	}

	return false;

}


/**
 * Stripe Disconnect URL
 */
function give_stripe_disconnect_url() {

	// Prepare Stripe Disconnect URL.
	$link = add_query_arg(
		array(
			'stripe_action'  => 'disconnect',
			'stripe_user_id' => give_get_option( 'give_stripe_user_id' ),
			'return_url'     => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
		),
		'https://connect.givewp.com/stripe/connect.php'
	);

	return apply_filters( 'give_stripe_disconnect_url', $link );

}

/**
 * Disconnects user from the Give Stripe Connected App.
 */
function give_stripe_connect_deauthorize() {

	$get_vars = give_clean( $_GET ); // WPCS: input var ok.

	// Be sure only to deauthorize when param present.
	if ( ! isset( $get_vars['stripe_disconnected'] ) ) {
		return false;
	}

	// Show message if NOT disconnected.
	if (
		'false' === $get_vars['stripe_disconnected']
		&& isset( $get_vars['error_code'] )
	) {

		$give_stripe = Give_Stripe::get_instance();
		$message     = sprintf(
			/* translators: %s Error Message */
			__( '<strong>Error:</strong> Give could not disconnect from the Stripe API. Reason: %s', 'give-stripe' ),
			esc_html( $get_vars['error_message'] )
		);
		$give_stripe->add_admin_notice( 'prompt_disconnect', 'notice notice-warning give-stripe-disconnect-message', $message );

	}

	// If user disconnects, remove the options regardless.
	// They can always click reconnect even if connected.
	give_stripe_connect_delete_options();

}

add_action( 'admin_notices', 'give_stripe_connect_deauthorize' );


/**
 * Delete all the Give settings options for Stripe Connect.
 *
 * @since 1.5
 */
function give_stripe_connect_delete_options() {

	// Disconnection successful.
	// Remove the connect options within the db.
	give_delete_option( 'give_stripe_connected' );
	give_delete_option( 'give_stripe_user_id' );
	give_delete_option( 'live_secret_key' );
	give_delete_option( 'test_secret_key' );
	give_delete_option( 'live_publishable_key' );
	give_delete_option( 'test_publishable_key' );
}

/**
 * Add advanced Stripe settings.
 *
 * New tab under Settings > Advanced that allows users to use their own API key.
 *
 * @param array $settings List of settings.
 *
 * @return mixed
 */
function give_stripe_connect_add_advanced_settings( $settings ) {

	$current_section = give_get_current_setting_section();

	if ( 'stripe' !== $current_section ) {
		return $settings;
	}

	$user_api_keys_enabled = give_is_setting_enabled( give_get_option( 'stripe_user_api_keys' ) );
	if ( $user_api_keys_enabled ) {
		give_delete_option( 'give_stripe_connected' );
	}

	$stripe_fonts = give_get_option( 'stripe_fonts', 'google_fonts' );

	switch ( $current_section ) {
		case 'stripe':
			$settings = array(
				array(
					'id'   => 'give_title_stripe_advanced',
					'type' => 'title',
				),
				array(
					'name'    => __( 'Stripe API Keys', 'give-stripe' ),
					'desc'    => __( 'Enable if you would like to use your own API keys rather than Stripe connect.', 'give-stripe' ),
					'id'      => 'stripe_user_api_keys',
					'type'    => 'radio_inline',
					'default' => 'disabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-stripe' ),
						'disabled' => __( 'Disabled', 'give-stripe' ),
					),
				),
				array(
					'name' => __( 'Stripe JS Incompatibility', 'give-stripe' ),
					'desc' => __( 'If your site has problems with processing cards using Stripe JS, check this option to use a fallback method of processing.', 'give-stripe' ),
					'id'   => 'stripe_js_fallback',
					'type' => 'checkbox',
				),
				array(
					'name' => __( 'Stripe Styles', 'give-stripe' ),
					'desc' => __( 'Edit the properties above to match the look and feel of your WordPress theme. These styles will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. Any valid CSS property can be defined, however, it must be formatted as JSON, not CSS. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#element-options" target="_blank">article</a>.', 'give-stripe' ),
					'id'   => 'stripe_styles',
					'type' => 'stripe_styles_field',
					'css'  => 'width: 100%',
				),
				array(
					'name' => __( 'Stripe Fonts', 'give-stripe' ),
					'desc' => __( 'Select the type of font you want to load in Stripe Credit Card fields including Card Number, CVC and Expiration. For more information on Styling Stripe CC fields please see this <a href="https://stripe.com/docs/stripe-js/reference#stripe-elements" target="_blank">article</a>.', 'give-stripe' ),
					'id'   => 'stripe_fonts',
					'type' => 'radio_inline',
					'default' => 'google_fonts',
					'options' => array(
						'google_fonts'  => __( 'Google Fonts', 'give-stripe' ),
						'custom_fonts' => __( 'Custom Fonts', 'give-stripe' ),
					),
				),
				array(
					'name'          => __( 'Google Fonts URL', 'give-stripe' ),
					'desc'          => __( 'Please enter the Google Fonts URL which is applied to your theme to have the Stripe Credit Card fields reflect the same fonts.', 'give-stripe' ),
					'id'            => 'stripe_google_fonts_url',
					'type'          => 'text',
					'wrapper_class' => 'give-stripe-google-fonts-wrap ' . ( 'google_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
				),
				array(
					'name'          => __( 'Custom Fonts', 'give-stripe' ),
					'desc'          => __( 'Edit the font properties above to match the fonts of your WordPress theme. These font properties will be applied to Stripe Credit Card fields including Card Number, CVC and Expiration. However, it must be formatted as JSON, not CSS.', 'give-stripe' ),
					'wrapper_class' => 'give-stripe-custom-fonts-wrap ' . ( 'custom_fonts' !== $stripe_fonts ? 'give-hidden' : '' ),
					'id'            => 'stripe_custom_fonts',
					'type'          => 'textarea',
					'default'       => '{}',
				),
				array(
					'id'   => 'give_title_stripe_advanced',
					'type' => 'sectionend',
				),
			);
			break;
	} // End switch().


	// Output.
	return $settings;

}

add_filter( 'give_get_settings_advanced', 'give_stripe_connect_add_advanced_settings', 10, 1 );


/**
 * Advanced Stripe Styles field to manage theme stylings for Stripe CC fields.
 *
 * @param array  $field_options List of field options.
 * @param string $option_value  Option value.
 *
 * @since 2.1
 */
function give_stripe_admin_stripe_styles_field( $field_options, $option_value ) {

	$default_attributes = array(
		'rows' => 10,
		'cols' => 60,
	);
	$textarea_attributes = isset( $value['attributes'] ) ? $field_options['attributes'] : array();

	// Make sure empty textarea have default valid json data so that the textarea doesn't show error.
	$base_styles_value     = ! empty( $option_value['base'] ) ? $option_value['base'] : give_stripe_get_default_base_styles();
	$empty_styles_value    = ! empty( $option_value['empty'] ) ? $option_value['empty'] : '{}';
	$invalid_styles_value  = ! empty( $option_value['invalid'] ) ? $option_value['invalid'] : '{}';
	$complete_styles_value = ! empty( $option_value['complete'] ) ? $option_value['complete'] : '{}';
	
	?>
	<tr valign="top" <?php echo ! empty( $field_options['wrapper_class'] ) ? 'class="' . esc_attr( $field_options['wrapper_class'] ) . '"' : '' ?>>
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_html( $field_options['type'] ); ?>">
				<?php echo esc_attr( $field_options['title'] ); ?>
			</label>
		</th>
		<td class="give-forminp give-forminp-<?php echo esc_html( $field_options['type'] ); ?>">
			<div>
				<p>
					<strong><?php esc_attr_e( 'Base Styles', 'give-stripe' ); ?></strong>
				</p>
				<p>
					<textarea
						name="stripe_styles[base]"
						id="<?php echo esc_attr( $field_options['id'] ) . '_base'; ?>"
						style="<?php echo esc_attr( $field_options['css'] ); ?>"
						class="<?php echo esc_attr( $field_options['class'] ); ?>"
						<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
					><?php echo esc_textarea( $base_styles_value ); ?></textarea>
				</p>
			</div>
			<div>
				<p>
					<strong><?php esc_attr_e( 'Empty Styles', 'give-stripe' ); ?></strong>
				</p>
				<p>
					<textarea
						name="stripe_styles[empty]"
						id="<?php echo esc_attr( $field_options['id'] ) . '_empty'; ?>"
						style="<?php echo esc_attr( $field_options['css'] ); ?>"
						class="<?php echo esc_attr( $field_options['class'] ); ?>"
						<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
					>
						<?php echo esc_textarea( $empty_styles_value ); ?>
					</textarea>
				</p>
			</div>
			<div>
				<p>
					<strong><?php esc_attr_e( 'Invalid Styles', 'give-stripe' ); ?></strong>
				</p>
				<p>
					<textarea
						name="stripe_styles[invalid]"
						id="<?php echo esc_attr( $field_options['id'] ) . '_invalid'; ?>"
						style="<?php echo esc_attr( $field_options['css'] ); ?>"
						class="<?php echo esc_attr( $field_options['class'] ); ?>"
						<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
					>
						<?php echo esc_textarea( $invalid_styles_value ); ?>
					</textarea>
				</p>
			</div>
			<div>
				<p>
					<strong><?php esc_attr_e( 'Complete Styles', 'give-stripe' ); ?></strong>
				</p>
				<p>
					<textarea
						name="stripe_styles[complete]"
						id="<?php echo esc_attr( $field_options['id'] ) . '_complete'; ?>"
						style="<?php echo esc_attr( $field_options['css'] ); ?>"
						class="<?php echo esc_attr( $field_options['class'] ); ?>"
						<?php echo give_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
					>
						<?php echo esc_textarea( $complete_styles_value ); ?>
					</textarea>
				</p>
			</div>
			<p class="give-field-description">
				<?php echo $field_options['desc']; ?>
			</p>
		</td>
	</tr>
	<?php
}

add_action( 'give_admin_field_stripe_styles_field', 'give_stripe_admin_stripe_styles_field', 10, 2 );


/**
 * Add "Stripe" advanced settings.
 *
 * @param array $section List of sections.
 *
 * @return mixed
 */
function give_stripe_connect_add_advanced_section( $section ) {
	$section['stripe'] = __( 'Stripe', 'give-stripe' );

	return $section;
}

add_filter( 'give_get_sections_advanced', 'give_stripe_connect_add_advanced_section' );

/**
 * Do something on sacing user api keys.
 *
 * @param array  $update_options New Option.
 * @param string $option_name    Option Name.
 * @param array  $old_options    Old Options.
 *
 * @since 2.0
 */
function give_stripe_save_user_api_keys( $update_options, $option_name, $old_options ) {

	// Delete connect options when api settings switched from disabled to enabled.
	if (
		isset( $update_options['stripe_user_api_keys'] ) &&
		give_is_setting_enabled( $update_options['stripe_user_api_keys'] ) &&
		! isset( $old_options['stripe_user_api_keys'] )
	) {
		give_stripe_connect_delete_options();
	}
}
add_action( 'give_save_settings_give_settings', 'give_stripe_save_user_api_keys', 10, 3 );
