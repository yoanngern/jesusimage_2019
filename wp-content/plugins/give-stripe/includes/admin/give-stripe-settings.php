<?php


/**
 * Class Give_Stripe_Settings
 *
 * @since 1.0
 */
class Give_Stripe_Settings {

	/**
	 * @access private
	 * @var Give_Stripe_Settings $instance
	 */
	static private $instance;

	/**
	 * @access private
	 * @var string $section_id
	 */
	private $section_id;

	/**
	 * @access private
	 *
	 * @var string $section_label
	 */
	private $section_label;

	/**
	 * Give_Stripe_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * get class object.
	 *
	 * @return Give_Stripe_Settings
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Setup hooks.
	 */
	public function setup_hooks() {

		$this->section_id    = 'stripe';
		$this->section_label = __( 'Stripe', 'give-stripe' );

		if ( is_admin() ) {
			// Add settings.
			add_action( 'give_admin_field_stripe_connect', array( $this, 'stripe_connect_field' ), 10, 2 );
			add_action( 'give_admin_field_stripe_configure_apple_pay', array( $this, 'stripe_configure_apple_pay_field' ), 10, 2 );
			add_filter( 'give_get_sections_gateways', array( $this, 'register_sections' ) );
			add_filter( 'give_get_settings_gateways', array( $this, 'register_settings' ) );
			add_filter( 'give_log_types', array( $this, 'set_stripe_log_type' ) );
			add_filter( 'give_log_views', array( $this, 'set_stripe_log_section' ) );
			add_action( 'give_logs_view_stripe', array( $this, 'give_stripe_logs_view' ) );
		}
	}

	/**
	 * Register sections.
	 *
	 * @acess public
	 *
	 * @param $sections
	 *
	 * @return mixed
	 */
	public function register_sections( $sections ) {
		$sections['stripe-settings']     = __( 'Stripe Settings', 'give-stripe' );
		$sections['stripe-ach-settings'] = __( 'Stripe + Plaid Settings', 'give-stripe' );

		return $sections;
	}

	/**
	 * Register Stripe Main Settings.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function register_settings( $settings ) {

		switch ( give_get_current_setting_section() ) {
			case 'stripe-settings':
				$settings = array(
					array(
						'id'   => 'give_title_stripe',
						'type' => 'title',
					),
					array(
						'name'          => __( 'Stripe Connect', 'give-stripe' ),
						'desc'          => '',
						'wrapper_class' => 'give-stripe-connect-tr',
						'id'            => 'stripe_connect',
						'type'          => 'stripe_connect',
					),
					array(
						'name'          => __( 'Live Secret Key', 'give-stripe' ),
						'desc'          => __( 'Enter your live secret key, found in your Stripe Account Settings.', 'give-stripe' ),
						'id'            => 'live_secret_key',
						'type'          => 'api_key',
						'wrapper_class' => 'give-stripe-key',
					),
					array(
						'name'          => __( 'Live Publishable Key', 'give-stripe' ),
						'desc'          => __( 'Enter your live publishable key, found in your Stripe Account Settings.', 'give-stripe' ),
						'id'            => 'live_publishable_key',
						'type'          => 'text',
						'wrapper_class' => 'give-stripe-key',
					),
					array(
						'name'          => __( 'Test Secret Key', 'give-stripe' ),
						'desc'          => __( 'Enter your test secret key, found in your Stripe Account Settings.', 'give-stripe' ),
						'id'            => 'test_secret_key',
						'type'          => 'api_key',
						'wrapper_class' => 'give-stripe-key',
					),
					array(
						'name'          => __( 'Test Publishable Key', 'give-stripe' ),
						'desc'          => __( 'Enter your test publishable key, found in your Stripe Account Settings.', 'give-stripe' ),
						'id'            => 'test_publishable_key',
						'type'          => 'text',
						'wrapper_class' => 'give-stripe-key',
					),
					array(
						'name'       => __( 'Statement Descriptor', 'give-stripe' ),
						'desc'       => __( 'This is the text that appears on your donor\'s bank statements. Statement descriptors are limited to 22 characters, cannot use the special characters <code><</code>, <code>></code>, <code>\'</code>, or <code>"</code>, and must not consist solely of numbers. This is typically the name of your website or organization.', 'give-stripe' ),
						'id'         => 'stripe_statement_descriptor',
						'type'       => 'text',
						'attributes' => array(
							'maxlength'   => '22',
							'placeholder' => get_bloginfo( 'name' ),
						),
						'default'    => get_bloginfo( 'name' ),
					),
					array(
						'name'    => __( '3D Secure Payments', 'give-stripe' ),
						'desc'    => sprintf(
							/* translators: 1. Stripe 3D Secure URL */
							__( 'This option will enable <a href="%1$s" target="_blank">3D secure card payments</a>, an additional layer of authentication that protects you from liability for fraudulent card payments. If enabled, donors may be redirected to the card issuer\'s site to verify their identity.', 'give-stripe' ),
							esc_url_raw( 'http://docs.givewp.com/stripe-three-d-secure' )
						),
						'id'      => 'stripe_enable_three_d_secure_payments',
						'type'    => 'checkbox',
						'default' => '',
						'wrapper_class' => 'give-stripe-three-d-secure-field-wrap',
					),
					array(
						'name' => __( 'Collect Billing Details', 'give-stripe' ),
						'desc' => __( 'This option will enable the billing details section for Stripe which requires the donor\'s address to complete the donation. These fields are not required by Stripe to process the transaction, but you may have the need to collect the data.', 'give-stripe' ),
						'id'   => 'stripe_collect_billing',
						'type' => 'checkbox',
					),
					array(
						'name' => __( 'Preapprove Only?', 'give-stripe' ),
						'desc' => __( 'Check this if you would like to preapprove payments but <strong>not charge until up to seven days</strong> after the donation has been made. Note: Preapproval does not work for Recurring donations or ACH (bank account) payments.', 'give-stripe' ),
						'id'   => 'stripe_preapprove_only',
						'type' => 'checkbox',
					),
					array(
						'name' => __( 'Enable Apple Pay and Google Pay', 'give-stripe' ),
						'desc' => __( 'This option will enable Apple Pay on Apple Devices and Google Pay on Chrome and Android devices.', 'give-stripe' ),
						'id'   => 'stripe_enable_apple_google_pay',
						'wrapper_class' => 'stripe-payment-request-setting-wrap',
						'type' => 'checkbox',
					),
					array(
						'name'          => __( 'Configure Apple Pay', 'give-stripe' ),
						'desc'          => 'This option will help you configure Apple Pay with Stripe with just a single click.',
						'wrapper_class' => 'give-stripe-configure-apple-pay',
						'id'            => 'stripe_configure_apple_pay',
						'type'          => 'stripe_configure_apple_pay',
					),
					array(
						'name'          => __( 'Apple and Google Pay Button Appearance', 'give-stripe' ),
						'desc'          => __( 'Adjust the appearance of the button style for Google and Apple pay.', 'give-stripe' ),
						'id'            => 'stripe_payment_request_button_style',
						'wrapper_class' => 'stripe-payment-request-button-style-wrap',
						'type'          => 'radio_inline',
						'default'       => 'dark',
						'options'       => array(
							'light'         => __( 'Light', 'give-stripe' ),
							'light-outline' => __( 'Light Outline', 'give-stripe' ),
							'dark'          => __( 'Dark', 'give-stripe' ),
						),
					),
					array(
						'name' => __( 'Enable Stripe Checkout', 'give-stripe' ),
						'desc' => sprintf( __( 'This option will enable <a href="%s" target="_blank">Stripe\'s modal checkout</a> where the donor will complete the donation rather than the default credit card fields on page. Note: Apple and Google pay do not work with the modal checkout option.', 'give-stripe' ), 'http://docs.givewp.com/stripe-checkout' ),
						'id'   => 'stripe_checkout_enabled',
						'type' => 'checkbox',
					),
					array(
						'name'          => __( 'Credit Card Fields Format', 'give-stripe' ),
						'desc'          => __( 'This option will enable you to show single or multiple credit card fields on your donation form for Stripe Payment Gateway.', 'give-stripe' ),
						'id'            => 'stripe_cc_fields_format',
						'wrapper_class' => 'stripe-cc-field-format-settings ' . $this->stripe_modal_checkout_status( 'disabled' ),
						'type'          => 'radio_inline',
						'default'       => 'multi',
						'options'       => array(
							'single' => __( 'Single Field', 'give-stripe' ),
							'multi'  => __( 'Multi Field', 'give-stripe' ),
						),
					),
					array(
						'name'          => __( 'Checkout Heading', 'give-stripe' ),
						'desc'          => __( 'This is the main heading within the modal checkout. Typically, this is the name of your organization, cause, or website.', 'give-stripe' ),
						'id'            => 'stripe_checkout_name',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => get_bloginfo( 'name' ),
						'type'          => 'text',
					),
					array(
						'name'          => __( 'Stripe Checkout Image', 'give-stripe' ),
						'desc'          => __( 'This image appears in when the Stripe checkout modal window opens and provides better brand recognition that leads to increased conversion rates. The recommended minimum size is a square image at 128x128px. The supported image types are: .gif, .jpeg, and .png.', 'give-stripe' ),
						'id'            => 'stripe_checkout_image',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'type'          => 'file',
						// Optional.
						'options'       => array(
							'url' => false, // Hide the text input for the url.
						),
						'text'          => array(
							'add_upload_file_text' => __( 'Add or Upload Image', 'give-stripe' ),
						),
					),
					array(
						'name'          => __( 'Processing Text', 'give-stripe' ),
						'desc'          => __( 'This text appears briefly after the donor has made a successful donation while Give is confirming the payment with the Stripe API.', 'give-stripe' ),
						'id'            => 'stripe_checkout_processing_text',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => __( 'Processing Donation...', 'give-stripe' ),
						'type'          => 'text',
					),
					array(
						'name'          => __( 'Verify Zip Code', 'give-stripe' ),
						'desc'          => __( 'Specify whether Checkout should validate the billing ZIP code of the donor for added fraud protection.', 'give-stripe' ),
						'id'            => 'stripe_checkout_zip_verify',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => 'on',
						'type'          => 'checkbox',
					),
					array(
						'name'          => __( 'Remember Me', 'give-stripe' ),
						'desc'          => __( 'Specify whether to include the option to "Remember Me" for future donations.', 'give-stripe' ),
						'id'            => 'stripe_checkout_remember_me',
						'wrapper_class' => 'stripe-checkout-field ' . $this->stripe_modal_checkout_status(),
						'default'       => 'on',
						'type'          => 'checkbox',
					),
					array(
						'name'  => __( 'Stripe Gateway Documentation', 'give-stripe' ),
						'id'    => 'display_settings_docs_link',
						'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
						'title' => __( 'Stripe Gateway Documentation', 'give-stripe' ),
						'type'  => 'give_docs_link',
					),
					array(
						'id'   => 'give_title_stripe',
						'type' => 'sectionend',
					),
				);

				break;

			case 'stripe-ach-settings':
				$settings = array(
					array(
						'id'   => 'give_title_stripe_ach',
						'type' => 'title',
					),
					array(
						'name'    => __( 'API Mode', 'give-stripe' ),
						'desc'    => sprintf(
							/* translators: %s Plaid API Host Documentation URL */
							__( 'Plaid has several API modes for testing and live transactions. "Test" mode allows you to test with a single sample bank account. "Development" mode allows you to accept up to 100 live donations without paying. "Live" mode allows for unlimited donations. Read the <a target="_blank" title="Plaid API Docs" href="%1$s">Plaid API docs</a> for more information.', 'give-stripe' ),
							esc_url( 'https://plaid.com/docs/api/#api-host' )
						),
						'id'      => 'plaid_api_mode',
						'type'    => 'radio_inline',
						'default' => 'sandbox',
						'options' => array(
							'sandbox'     => __( 'Test', 'give-stripe' ),
							'development' => __( 'Development', 'give-stripe' ),
							'production'  => __( 'Live', 'give-stripe' ),
						),
					),
					array(
						'name' => __( 'Plaid Client ID', 'give-stripe' ),
						'desc' => __( 'Enter your Plaid Client ID, found in your Plaid account dashboard.', 'give-stripe' ),
						'id'   => 'plaid_client_id',
						'type' => 'text',
					),
					array(
						'name' => __( 'Plaid Public Key', 'give-stripe' ),
						'desc' => __( 'Enter your Plaid public key, found in your Plaid account dashboard.', 'give-stripe' ),
						'id'   => 'plaid_public_key',
						'type' => 'text',
					),
					array(
						'name' => __( 'Plaid Secret Key', 'give-stripe' ),
						'desc' => __( 'Enter your Plaid secret key, found in your Plaid account dashboard.', 'give-stripe' ),
						'id'   => 'plaid_secret_key',
						'type' => 'api_key',
					),
					array(
						'id'   => 'give_title_stripe_ach',
						'type' => 'sectionend',
					),
				);

				break;
		}// End switch().

		return $settings;
	}

	/**
	 * This function return hidden for fields which should get hidden on toggle of modal checkout checkbox.
	 *
	 * @param string $status Status - Enabled or Disabled.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return string
	 */
	public function stripe_modal_checkout_status( $status = 'enabled' ) {
		$stripe_checkout = give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled', 'disabled' ) );

		if (
			( $stripe_checkout && 'disabled' === $status ) ||
			( ! $stripe_checkout && 'enabled' === $status )
		) {
			return 'give-hidden';
		}

		return '';
	}


	/**
	 * Connect field
	 *
	 * @param $value
	 * @param $option_value
	 */
	function stripe_connect_field( $value, $option_value ) {

		// If the user wants to use their own API keys they can.
		$user_api_keys_enabled = give_is_setting_enabled( give_get_option( 'stripe_user_api_keys' ) );
		if ( $user_api_keys_enabled ) : ?>
			<style>
				.give-stripe-connect-tr {
					display: none;
				}
			</style>
		<?php else : ?>
			<style>
				.stripe-checkout-field, .give-stripe-key {
					display: none;
				}
			</style>
		<?php endif; ?>
		<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . esc_attr( $value['wrapper_class'] ) . '"' : '' ?>>
			<th scope="row" class="titledesc">
				<label for="test_secret_key"> <?php esc_attr_e( 'Stripe Connection', 'give-stripe' ) ?></label>
			</th>
			<?php if ( give_is_stripe_connected() ) :
				$stripe_user_id = give_get_option( 'give_stripe_user_id' );
				?>

				<td class="give-forminp give-forminp-api_key">
					<span id="give-stripe-connect" class="stripe-btn-disabled"><span>Connected</span></span>
					<p class="give-field-description">
						<span class="dashicons dashicons-yes" style="color:#25802d;"></span>
						<?php
						esc_attr_e( 'Stripe is connected.', 'give-stripe' );
						$disconnect_confirmation_message = sprintf(
							/* translators: %s Stripe User ID */
							__( 'Are you sure you want to disconnect Give from Stripe? If disconnected, this website and any others sharing the same Stripe account (%s) that are connected to Give will need to reconnect in order to process payments.', 'give-stripe' ),
							$stripe_user_id
						);
						?>
						<a href="<?php echo esc_url( give_stripe_disconnect_url() ); ?>" class="give-stripe-disconnect"
							onclick="return confirm('<?php echo esc_html( $disconnect_confirmation_message ); ?>');">[Disconnect]</a>
					</p>
				</td>


			<?php else : ?>
				<td class="give-forminp give-forminp-api_key">
					<?php echo give_stripe_connect_button(); ?>
					<p class="give-field-description">
						<span class="dashicons dashicons-no"
						      style="color:red;"></span><?php _e( 'Stripe is NOT connected.', 'give-stripe' ) ?>
					</p>
					<?php if ( isset( $_GET['error_code'] ) && isset( $_GET['error_message'] ) ) : ?>
						<p class="stripe-connect-error">
							<strong><?php echo give_clean( $_GET['error_code'] ); ?>:</strong> <?php echo give_clean( $_GET['error_message'] ); ?>
						</p>
					<?php endif; ?>
				</td>

			<?php endif; ?>

		</tr>
	<?php
	}

	/**
	 * Configure Apple Pay Field using Stripe.
	 *
	 * @param array  $value        List of values.
	 * @param string $option_value Option value.
	 *
	 * @since 2.0.8
	 */
	function stripe_configure_apple_pay_field( $value, $option_value ) {
		?>
		<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . esc_attr( $value['wrapper_class'] ) . '"' : '' ?>>
			<th scope="row" class="titledesc">
				<label for="configure_apple_pay">
					<?php esc_attr_e( 'Configure Apple Pay', 'give-stripe' ) ?>
				</label>
			</th>
			<td class="give-forminp give-forminp-api_key">
				<?php
				$is_apple_pay_registered = give_get_option( 'is_stripe_apple_pay_registered' );
				if ( $is_apple_pay_registered ) {
					?>
					<span id="give-stripe-configure-apple-pay" class="stripe-btn-disabled">
						<span><?php esc_attr_e( 'Domain Registered', 'give-stripe' ); ?></span>
					</span>
					<span id="give-stripe-reset-apple-pay">
						<a href="<?php echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&give_action=reset_stripe_apple_pay_domain' ) ); ?>">
							<?php esc_attr_e( 'Reset', 'give-stripe' ); ?>
						</a>
					</span>
					<?php
				} else {
					?>
					<a href="<?php echo esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&give_action=register_stripe_apple_pay_domain' ) ); ?>" class="give-stripe-register-domain-btn">
						<?php esc_attr_e( 'Register Domain', 'give-stripe' ); ?>
					</a>
					<?php
				}
				?>
				<p class="give-field-description">
					<?php esc_attr_e( 'This option will help you register your domain for Apple Pay using Stripe.', 'give-stripe' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * This function will set new stripe log type as valid log type.
	 *
	 * @param array $types List of log types.
	 *
	 * @since  2.0.8
	 * @access public
	 *
	 * @return array
	 */
	public function set_stripe_log_type( $types ) {

		$new_log_type = array( 'stripe' );

		return array_merge( $types, $new_log_type );
	}

	/**
	 * This function will set new stripe log section.
	 *
	 * @param array $sections List of log sections.
	 *
	 * @since  2.0.8
	 * @access public
	 *
	 * @return array
	 */
	public function set_stripe_log_section( $sections ) {

		$new_log_section = array(
			'stripe' => __( 'Stripe', 'give-stripe' ),
		);

		return array_merge( $sections, $new_log_section );
	}


	/**
	 * Stripe Logs View
	 *
	 * @since 2.0.8
	 *
	 * @return void
	 */
	function give_stripe_logs_view() {
		include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/class-give-stripe-logs-list-table.php' );

		$logs_table = new Give_Stripe_Log_Table();
		$logs_table->prepare_items();
		?>
		<div class="wrap">

			<?php
			/**
			 * Fires before displaying Payment Error logs.
			 *
			 * @since 2.0.8
			 */
			do_action( 'give_stripe_logs_top' );

			$logs_table->display(); ?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-tools"/>
			<input type="hidden" name="tab" value="logs"/>
			<input type="hidden" name="section" value="stripe"/>

			<?php
			/**
			 * Fires after displaying update logs.
			 *
			 * @since 2.0.8
			 */
			do_action( 'give_stripe_logs_bottom' );
			?>

		</div>
		<?php
	}


}

Give_Stripe_Settings::get_instance()->setup_hooks();
