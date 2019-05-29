<?php
/**
 * Stripe Helper Functions
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to
 * prevent them from getting posted to the server.
 *
 * @param int  $form_id Donation Form ID.
 * @param int  $args    Donation Form Arguments.
 * @param bool $echo    Status to display or not.
 *
 * @access public
 * @since  1.0
 *
 * @return string $form
 */
function give_stripe_credit_card_form( $form_id, $args, $echo = true ) {

	// No CC or billing fields for popup.
	$stripe_gateway = new Give_Stripe_Gateway();
	if ( $stripe_gateway->is_stripe_popup_enabled() ) {
		return false;
	}

	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

	$fallback_option    = give_get_option( 'stripe_js_fallback' );
	$stripe_js_fallback = ! empty( $fallback_option );

	$stripe_cc_field_format = give_get_option( 'stripe_cc_fields_format', 'multi' );

	// Get User Agent.
	$user_agent = give_get_user_agent();

	ob_start();

	do_action( 'give_before_cc_fields', $form_id ); ?>

	<fieldset id="give_cc_fields" class="give-do-validate">

		<?php
		// Display Payment Request Button only if Apple/Google Pay is enabled else display label.
		if ( ! give_stripe_is_apple_google_pay_enabled() ) {
			?>
			<legend><?php esc_attr_e( 'Credit Card Info', 'give-stripe' ); ?></legend>
			<?php
		} else {
			?>
			<ul class="give-stripe-payment-tabs give-clearfix">
				<li class="active">
					<a class="give-stripe-tab-element" href="#give-stripe-credit-card-content">
					<span class="give-stripe-card-icon">
						<img src="<?php echo GIVE_STRIPE_PLUGIN_URL . 'assets/dist/images/credit-card.svg'; ?>" alt="<?php _e( 'Credit Card', 'give-stirpe' ) ?>" />
					</span>
						<span class="give-stripe-card-text"><?php esc_attr_e( 'Credit Card', 'give-stripe' ); ?></span>
					</a>
				</li>
				<?php
				if ( preg_match( '/Chrome[\/\s](\d+\.\d+)/', $user_agent ) ) {
					?>
					<li>
						<a class="give-stripe-tab-element" href="#give-stripe-payment-request-content">
						<span class="give-stripe-payment-request-icon">
							<img src="<?php echo GIVE_STRIPE_PLUGIN_URL . 'assets/dist/images/google-pay.svg'; ?>" alt="<?php _e( 'Google Pay', 'give-stirpe' ) ?>" />
						</span>
						</a>
					</li>

					<?php
				} elseif ( preg_match( '/Safari[\/\s](\d+\.\d+)/', $user_agent ) ) {
					?>
					<li>
						<a class="give-stripe-tab-element" href="#give-stripe-payment-request-content">
							<span class="give-stripe-payment-request-icon">
								<img src="<?php echo GIVE_STRIPE_PLUGIN_URL . 'assets/dist/images/apple-pay.svg'; ?>" alt="<?php _e( 'Apple Pay', 'give-stirpe' ) ?>" />
							</span>
						</a>
					</li>
					<?php
				} // End if().

				?>
			</ul>
			<?php
		} // End if().
		?>
        <div id="give-stripe-credit-card-content" class="give-stripe-tab-content give-stripe-credit-card-content">
            <div class="give-stripe-cc-fields-container">
				<?php if ( is_ssl() ) : ?>
                    <div id="give_secure_site_wrapper">
                        <span class="give-icon padlock"></span>
                        <span>
					<?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give-stripe' ); ?>
				</span>
                    </div>
				<?php endif; ?>
				<?php
				if ( 'single' === $stripe_cc_field_format ) {

					// Display the stripe container which can be occupied by Stripe for CC fields.
					echo '<div id="give-stripe-single-cc-fields-' . esc_html( $id_prefix ) . '" class="give-stripe-single-cc-field-wrap"></div>';

				} elseif ( 'multi' === $stripe_cc_field_format ) {
					?>
                    <div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-stripe-cc-field-wrap">
                        <div>
                            <label for="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
								<?php esc_attr_e( 'Card Number', 'give-stripe' ); ?>
                                <span class="give-required-indicator">*</span>
                                <span class="give-tooltip give-icon give-icon-question"
                                      data-tooltip="<?php esc_attr_e( 'The (typically) 16 digits on the front of your credit card.', 'give-stripe' ); ?>"></span>
                                <span class="card-type"></span>
                            </label>
                            <div id="give-card-number-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-number-field"></div>
                        </div>
                    </div>

                    <div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
                        <div>
                            <label for="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
								<?php esc_attr_e( 'CVC', 'give-stripe' ); ?>
                                <span class="give-required-indicator">*</span>
                                <span class="give-tooltip give-icon give-icon-question"
                                      data-tooltip="<?php esc_attr_e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give-stripe' ); ?>"></span>
                            </label>
                            <div id="give-card-cvc-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-cvc-field"></div>
                        </div>
                    </div>

                    <div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
                        <label for="card_name" class="give-label">
							<?php esc_attr_e( 'Cardholder Name', 'give-stripe' ); ?>
                            <span class="give-required-indicator">*</span>
                            <span class="give-tooltip give-icon give-icon-question"
                                  data-tooltip="<?php esc_attr_e( 'The name of the credit card account holder.', 'give-stripe' ); ?>"></span>
                        </label>

                        <input
                                type="text"
                                autocomplete="off"
                                id="card_name"
                                name="card_name"
                                class="card-name give-input required"
                                placeholder="<?php esc_attr_e( 'Cardholder Name', 'give-stripe' ); ?>"
                        />
                    </div>

					<?php do_action( 'give_before_cc_expiration' ); ?>

                    <div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-stripe-cc-field-wrap">
                        <div>
                            <label for="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
								<?php esc_attr_e( 'Expiration', 'give-stripe' ); ?>
                                <span class="give-required-indicator">*</span>
                                <span class="give-tooltip give-icon give-icon-question"
                                      data-tooltip="<?php esc_attr_e( 'The date your credit card expires, typically on the front of the card.', 'give-stripe' ); ?>"></span>
                            </label>

                            <div id="give-card-expiration-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-cc-field give-stripe-card-expiration-field"></div>
                        </div>
                    </div>
					<?php
				} // End if().
				?>
            </div>
			<?php
			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
             * Note: Kept this hook as it is.
             *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'give_after_cc_expiration', $form_id, $args );

			/**
			 * This action hook is used to display content after the Credit Card expiration field.
			 *
			 * @param int   $form_id Donation Form ID.
			 * @param array $args    List of additional arguments.
			 */
			do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
			?>
        </div>
    </fieldset>
	<?php
	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}

add_action( 'give_stripe_cc_form', 'give_stripe_credit_card_form', 10, 3 );

/**
 * This function will add billing address to the credit card fields only.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *
 * @since 2.2.0
 */
function give_stripe_add_billing_address_to_cc_fields( $form_id, $args ) {

	// Remove Address Fields if user has option enabled.
	$billing_fields_enabled = give_get_option( 'stripe_collect_billing' );
	if ( ! $billing_fields_enabled ) {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}

	do_action( 'give_after_cc_fields', $form_id, $args );
}

add_action( 'give_stripe_after_cc_expiration', 'give_stripe_add_billing_address_to_cc_fields', 10, 2 );

/**
 * Display Payment Request Button at the bottom.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *
 * @since 2.0.5
 */
function give_stripe_display_payment_request_button( $form_id, $args ) {

	$user_agent         = give_get_user_agent();
	$id_prefix          = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';
	$is_billing_enabled = give_get_option( 'stripe_collect_billing' );

	// Don't display payment request HTML markup when other gateways are selected.
	if ( 'stripe' !== give_get_chosen_gateway( $form_id ) ) {
		return;
	}

	// Display Payment Request Button only of Apple/Google Pay is enabled.
	if ( ! give_is_stripe_checkout_enabled() && give_stripe_is_apple_google_pay_enabled() ) {
		?>
		<div id="give-stripe-payment-request-content" class="give-stripe-tab-content give-stripe-payment-request-content">
			<div id="give-stripe-payment-request-button-wrap" class="give-stripe-payment-request-button-wrap">
                <?php
                // Enable billing fields for Payment Request when the collect billing address is disabled.
                if ( ! $is_billing_enabled ) {
                    ?>
                    <div class="give-stripe-address-fields give-hidden">
                        <select name="billing_country">
			                <?php
			                foreach (give_get_country_list() as $key => $value) {
				                ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				                <?php
			                }
			                ?>
                        </select>
                        <div id="give-card-state-wrap">
                            <select name="card_state"></select>
                        </div>
                        <input type="hidden" name="card_address">
                        <input type="hidden" name="card_address_2">
                        <input type="hidden" name="card_city">
                        <input type="hidden" name="card_zip">
                    </div>
	                <?php
                }
                ?>
				<div id="give-stripe-payment-request-button-<?php echo esc_html( $id_prefix ); ?>" class="give-stripe-payment-request-button give-hidden">
					<div class="give_error">
						<p>
							<strong><?php echo __( 'ERROR:', 'give-stripe' ); ?></strong>
							<?php
							if ( ! is_ssl() ) {
								esc_attr_e( 'In order to donate using Apple or Google Pay the connection needs to be secure. Please visit the secure donation URL (https) to give using this payment method.', 'give-stripe' );
							} elseif ( preg_match( '/Chrome[\/\s](\d+\.\d+)/', $user_agent ) ) {
								esc_attr_e( 'Either you do not have a saved card to donate with G Pay or you\'re using an older version of Chrome without G Pay support.', 'give-stripe' );
							} elseif ( preg_match( '/Safari[\/\s](\d+\.\d+)/', $user_agent ) ) {
								esc_attr_e( 'Either your browser does not support Apple Pay or you do not have a saved payment method.', 'give-stripe' );
							}
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

add_action( 'give_donation_form_after_cc_form', 'give_stripe_display_payment_request_button', 9999, 2 );

/**
 * Add an errors div per form.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of Donation Arguments.
 *
 * @access public
 * @since  2.0.5
 *
 * @return void
 */
function give_stripe_add_stripe_errors( $form_id, $args ) {
	echo '<div id="give-stripe-payment-errors-' . esc_html( $args['id_prefix'] ) . '"></div>';
}

add_action( 'give_donation_form_after_cc_form', 'give_stripe_add_stripe_errors', 8899, 2 );

/**
 * Get the meta key for storing Stripe customer IDs in.
 *
 * @access      public
 * @since       1.0
 * @return      string $key
 */
function give_stripe_get_customer_key() {

	$key = '_give_stripe_customer_id';
	if ( give_is_test_mode() ) {
		$key .= '_test';
	}

	return $key;
}

/**
 * Determines if the shop is using a zero-decimal currency.
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function give_stripe_is_zero_decimal_currency() {

	$ret      = false;
	$currency = give_get_currency();

	switch ( $currency ) {

		case 'BIF' :
		case 'CLP' :
		case 'DJF' :
		case 'GNF' :
		case 'JPY' :
		case 'KMF' :
		case 'KRW' :
		case 'MGA' :
		case 'PYG' :
		case 'RWF' :
		case 'VND' :
		case 'VUV' :
		case 'XAF' :
		case 'XOF' :
		case 'XPF' :

			$ret = true;
			break;

	}

	return $ret;
}


/**
 * Use give_get_payment_transaction_id() first.
 *
 * Given a Payment ID, extract the transaction ID from Stripe and update the payment meta.
 *
 * @param string $payment_id Payment ID.
 *
 * @return string                   Transaction ID
 */
function give_stripe_get_payment_txn_id_fallback( $payment_id ) {

	$notes          = give_get_payment_notes( $payment_id );
	$transaction_id = '';

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$transaction_id = $match[1];
			update_post_meta( $payment_id, '_give_payment_transaction_id', $transaction_id );
			continue;
		}
	}

	return apply_filters( 'give_stripe_get_payment_txn_id_fallback', $transaction_id, $payment_id );
}

add_filter( 'give_get_payment_transaction_id-stripe', 'give_stripe_get_payment_txn_id_fallback', 10, 1 );
add_filter( 'give_get_payment_transaction_id-stripe_ach', 'give_stripe_get_payment_txn_id_fallback', 10, 1 );


/**
 * Get Statement Descriptor.
 *
 * Create the Statement Description.
 *
 * @see https://stripe.com/docs/api/php#create_charge-statement_descriptor
 *
 * @since 1.3
 *
 * @param array $data List of posted variable while submitting donation.
 *
 * @return mixed
 */
function give_get_stripe_statement_descriptor( $data = array() ) {

	$descriptor_option = give_get_option( 'stripe_statement_descriptor', get_bloginfo( 'name' ) );

	// Clean the statement descriptor.
	$unsupported_characters = array( '<', '>', '"', '\'' );
	$statement_descriptor   = mb_substr( $descriptor_option, 0, 22 );
	$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

	return apply_filters( 'give_stripe_statement_descriptor', $statement_descriptor, $data );

}


/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet.
 *
 * @since  1.4
 *
 * @param  int $user_id_or_email The user ID or email to look up.
 *
 * @return string       Stripe customer ID.
 */
function give_stripe_get_customer_id( $user_id_or_email ) {

	$user_id            = 0;
	$stripe_customer_id = '';

	// First check the customer meta of purchase email.
	if ( class_exists( 'Give_DB_Donor_Meta' ) && is_email( $user_id_or_email ) ) {
		$donor              = new Give_Donor( $user_id_or_email );
		$stripe_customer_id = $donor->get_meta( give_stripe_get_customer_key() );
	}

	// If not found via email, check user_id.
	if ( class_exists( 'Give_DB_Donor_Meta' ) && empty( $stripe_customer_id ) ) {
		$donor              = new Give_Donor( $user_id, true );
		$stripe_customer_id = $donor->get_meta( give_stripe_get_customer_key() );
	}

	// Get user ID from customer.
	if ( is_email( $user_id_or_email ) && empty( $stripe_customer_id ) ) {

		$donor = new Give_Donor( $user_id_or_email );
		// Pull user ID from customer object.
		if ( $donor->id > 0 && ! empty( $donor->user_id ) ) {
			$user_id = $donor->user_id;
		}
	} else {
		// This is a user ID passed.
		$user_id = $user_id_or_email;
	}

	// If no Stripe customer ID found in customer meta move to wp user meta.
	if ( empty( $stripe_customer_id ) && ! empty( $user_id ) ) {

		$stripe_customer_id = get_user_meta( $user_id, give_stripe_get_customer_key(), true );

	} elseif ( empty( $stripe_customer_id ) && class_exists( 'Give_Recurring_Subscriber' ) ) {

		// Not found in customer meta or user meta, check Recurring data.
		$by_user_id = is_int( $user_id_or_email ) ? true : false;
		$subscriber = new Give_Recurring_Subscriber( $user_id_or_email, $by_user_id );

		if ( $subscriber->id > 0 ) {

			$verified = false;

			if ( ( $by_user_id && $user_id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber.
				$verified = true;
			} else {
				// If the email used is the same as the primary email.
				if ( $subscriber->email == $user_id_or_email ) {
					$verified = true;
				}

				// If the email is in the Give's Additional emails.
				if ( property_exists( $subscriber, 'emails' ) && in_array( $user_id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}

			if ( $verified ) {

				// Backwards compatibility from changed method name.
				// We changed the method name in recurring.
				if ( method_exists( $subscriber, 'get_recurring_donor_id' ) ) {
					$stripe_customer_id = $subscriber->get_recurring_donor_id( 'stripe' );
				} elseif(method_exists($subscriber, 'get_recurring_customer_id')) {
					$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
				}
			}
		}

		if ( ! empty( $stripe_customer_id ) ) {
			update_user_meta( $subscriber->user_id, give_stripe_get_customer_key(), $stripe_customer_id );
		}
	}// End if().

	return $stripe_customer_id;

}

/**
 * Process refund in Stripe.
 *
 * @access      public
 * @since       1.4
 *
 * @param $payment_id
 * @param $new_status
 * @param $old_status
 *
 * @return      void
 */
function give_stripe_process_refund( $payment_id, $new_status, $old_status ) {

	// Only move forward if refund requested.
	if ( empty( $_POST['give_refund_in_stripe'] ) ) {
		return;
	}

	// Verify statuses.
	$should_process_refund = 'publish' != $old_status ? false : true;
	$should_process_refund = apply_filters( 'give_stripe_should_process_refund', $should_process_refund, $payment_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' !== $new_status ) {
		return;
	}

	$charge_id = give_get_payment_transaction_id( $payment_id );

	// If no charge ID, look in the payment notes.
	if ( empty( $charge_id ) || $charge_id == $payment_id ) {
		$charge_id = give_stripe_get_payment_txn_id_fallback( $payment_id );
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		return;
	}

	$stripe_gateway = new Give_Stripe_Gateway();

	try {

		$refund = \Stripe\Refund::create( array(
			'charge' => $charge_id,
		) );

		if ( isset( $refund->id ) ) {
			give_insert_payment_note(
				$payment_id,
				sprintf(
					/* translators: 1. Refund ID */
					esc_html__( 'Charge refunded in Stripe: %s', 'give-stripe' ),
					$refund->id
				)
			);
		}
	} catch ( \Stripe\Error\Base $e ) {
		// Refund issue occurred.
		$log_message = __( 'The Stripe payment gateway returned an error while refunding a donation.', 'give-stripe' ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Message: %s', 'give-stripe' ), $e->getMessage() ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Code: %s', 'give-stripe' ), $e->getCode() );

		// Log it with DB.
		give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), $log_message );

	} catch ( Exception $e ) {

		// some sort of other error.
		$body = $e->getJsonBody();
		$err  = $body['error'];

		if ( isset( $err['message'] ) ) {
			$error = $err['message'];
		} else {
			$error = esc_html__( 'Something went wrong while refunding the charge in Stripe.', 'give-stripe' );
		}

		wp_die( $error, esc_html__( 'Error', 'give-stripe' ), array(
			'response' => 400,
		) );

	} // End try().

	do_action( 'give_stripe_donation_refunded', $payment_id );

}

add_action( 'give_update_payment_status', 'give_stripe_process_refund', 200, 3 );



/**
 * Get Donation Summary
 *
 * Creates a donation summary for payment gateways from the donation data before the payment is created in the database.
 *
 * @TODO: Remove after 2.0 release. This is only here as a shim for https://github.com/WordImpress/Give-Stripe/issues/111
 *
 * @since 1.5.2
 *
 * @param int|array $donation       Donation ID or List of posted variables.
 * @param bool      $name_and_email True, to include name and email. Default false.
 * @param int       $length         Length of donation summary.
 *
 * @return string
 */
function give_stripe_payment_gateway_donation_summary( $donation, $name_and_email = true, $length = 255 ) {

	// If $donation is numeric then consider it to be donation id.
	if ( is_numeric( $donation ) ) {

		$form_id    = give_get_payment_form_id( $donation );
		$price_id   = give_get_payment_meta( $donation, '_give_payment_price_id', true );
		$form_title = give_get_donation_form_title( $donation );

		// Prepare Donation Summary.
		$summary = ! empty( $form_id )
			? $form_title
			: ( ! empty( $form_id )
				? wp_sprintf(
					/* translators: Form ID. */
					__( 'Donation Form ID: %d', 'give-stripe' ),
					$form_id
				)
				: __( 'Untitled donation form', 'give-stripe' )
			);

		// Form multilevel if applicable.
		if ( ! empty( $price_id ) && 'custom' !== $price_id ) {
			$summary .= ': ' . give_get_price_option_name( $form_id, $price_id );
		}

		// Add Donor's name + email if requested.
		if ( $name_and_email ) {

			$donor_name  = give_get_donor_name_by( $donation, 'donation' );
			$donor_email = give_get_donation_donor_email( $donation );

			// Append Donor Name and Email to donation summary.
			$summary .= "- {$donor_name} ({$donor_email})";
		}

		// Cut the length.
		return substr( $summary, 0, $length );

	} else {
		return give_payment_gateway_donation_summary( $donation, $name_and_email, $length );
	} // End if().

}

/**
 * This function will prepare JSON for default base styles.
 *
 * @since 2.1
 *
 * @return mixed|string
 */
function give_stripe_get_default_base_styles() {

	$float_labels = give_is_float_labels_enabled(
		array(
			'form_id' => get_the_ID(),
		)
	);

	return json_encode( array(
		'color'             => '#32325D',
		'fontWeight'        => 500,
		'fontSize'          => '16px',
		'fontSmoothing'     => 'antialiased',
		'::placeholder'     => array(
			'color' => $float_labels ? '#CCCCCC' : '#222222',
		),
		':-webkit-autofill' => array(
			'color' => '#e39f48',
		),
	) );
}

/**
 * This function is used to get the stripe styles.
 *
 * @since 2.1
 *
 * @return mixed
 */
function give_stripe_get_stripe_styles() {

	$default_styles = array(
		'base'     => give_stripe_get_default_base_styles(),
		'empty'    => false,
		'invalid'  => false,
		'complete' => false,
	);

	return give_get_option( 'stripe_styles', $default_styles );
}

/**
 * Get Base Styles for Stripe Elements CC Fields.
 *
 * @since 1.6
 *
 * @return object
 */
function give_stripe_get_element_base_styles() {

	$stripe_styles = give_stripe_get_stripe_styles();
	$base_styles   = json_decode( $stripe_styles['base'] );

	return (object) apply_filters( 'give_stripe_get_element_base_styles', $base_styles );
}

/**
 * Get Complete Styles for Stripe Elements CC Fields.
 *
 * @since 2.1
 *
 * @return object
 */
function give_stripe_get_element_complete_styles() {

	$stripe_styles   = give_stripe_get_stripe_styles();
	$complete_styles = json_decode( $stripe_styles['complete'] );

	return (object) apply_filters( 'give_stripe_get_element_complete_styles', $complete_styles );
}

/**
 * Get Invalid Styles for Stripe Elements CC Fields.
 *
 * @since 2.1
 *
 * @return object
 */
function give_stripe_get_element_invalid_styles() {

	$stripe_styles  = give_stripe_get_stripe_styles();
	$invalid_styles = json_decode( $stripe_styles['invalid'] );

	return (object) apply_filters( 'give_stripe_get_element_invalid_styles', $invalid_styles );
}

/**
 * Get Empty Styles for Stripe Elements CC Fields.
 *
 * @since 2.1
 *
 * @return object
 */
function give_stripe_get_element_empty_styles() {

	$stripe_styles = give_stripe_get_stripe_styles();
	$empty_styles  = json_decode( $stripe_styles['empty'] );

	return (object) apply_filters( 'give_stripe_get_element_empty_styles', $empty_styles );
}

/**
 * Get Stripe Element Font Styles.
 *
 * @since 2.0.4
 *
 * @return string
 */
function give_stripe_get_element_font_styles() {

	$font_styles  = '';
	$stripe_fonts = give_get_option( 'stripe_fonts', 'google_fonts' );

	if ( 'custom_fonts' === $stripe_fonts ) {
		$custom_fonts_attributes = give_get_option( 'stripe_custom_fonts' );
		$font_styles = json_decode( $custom_fonts_attributes );
	} else {
		$font_styles = array(
			'cssSrc' => give_get_option( 'stripe_google_fonts_url' ),
		);
	}

	if ( empty( $font_styles ) ) {
		$font_styles = array();
	}

	return apply_filters( 'give_stripe_get_element_font_styles', $font_styles );

}

/**
 * Retrieve API endpoint.
 *
 * @since 2.0
 *
 * @return string
 */
function give_stripe_ach_get_api_endpoint() {

	/**
	 * This hook filter the result of api endpoint.
	 *
	 * @since 2.0
	 */
	return apply_filters(
		'give_stripe_ach_get_api_endpoint',
		give_get_option( 'plaid_api_mode', 'production' )
	);

}

/**
 * Get Endpoint URL by Token Type.
 *
 * @param string $token_type Get endpoint URL based on token type provided.
 *
 * @since 1.6
 *
 * @return string
 */
function give_stripe_ach_get_endpoint_url( $token_type = 'exchange' ) {

	$endpoint_url = esc_url( 'https://%1$s.plaid.com/item/public_token/exchange' );
	if ( 'bank_account' === $token_type ) {
		$endpoint_url = esc_url( 'https://%1$s.plaid.com/processor/stripe/bank_account_token/create' );
	}

	return sprintf(
		$endpoint_url,
		give_stripe_ach_get_api_endpoint()
	);
}

/**
 * Get Stripe ACH (Plaid) API Version.
 *
 * @since 1.6
 *
 * @return string
 */
function give_stripe_ach_get_current_api_version() {

	// Current API Version: v2.
	return 'v2';
}

/**
 * Get Plaid Checkout URL.
 *
 * @since 1.6
 *
 * @return string
 */
function give_stripe_ach_get_plaid_checkout_url() {
	return sprintf(
		esc_url( 'https://cdn.plaid.com/link/%1$s/stable/link-initialize.js' ),
		give_stripe_ach_get_current_api_version()
	);
}

/**
 * Check whether Apple Pay or Google pay settings is enabled or not.
 *
 * @since 1.6
 *
 * @return bool
 */
function give_stripe_is_apple_google_pay_enabled() {
	return give_is_setting_enabled( give_get_option( 'stripe_enable_apple_google_pay' ) );
}

/**
 * Get the sequential order number of donation.
 *
 * @since 2.0
 *
 * @param integer $donation_or_post_id Donation or wp post id.
 * @param bool    $check_enabled       Check if sequential-ordering_status is activated or not.
 *
 * @return bool|string
 */
function give_stripe_get_sequential_id( $donation_or_post_id, $check_enabled = true ) {
	// Check if enabled.
	if ( true === $check_enabled ) {
		$sequential_ordering = give_get_option( 'sequential-ordering_status' );

		if ( ! give_is_setting_enabled( $sequential_ordering ) ) {
			return false;
		}
	}

	return Give()->seq_donation_number->get_serial_code( $donation_or_post_id );
}

/**
 * Check whether the Stripe Checkout is enabled or not.
 *
 * @since 2.0
 *
 * @return bool
 */
function give_is_stripe_checkout_enabled() {
	return give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled' ) );
}

/**
 * Get Publishable Key.
 *
 * @since 2.0
 *
 * @return mixed
 */
function give_stripe_get_publishable_key() {

	$publishable_key = give_get_option( 'live_publishable_key' );

	if ( give_is_test_mode() ) {
		$publishable_key = give_get_option( 'test_publishable_key' );
	}

	return $publishable_key;
}

/**
 * Get Secret Key.
 *
 * @since 2.0
 *
 * @return mixed
 */
function give_stripe_get_secret_key() {

	$secret_key = give_get_option( 'live_secret_key' );

	if ( give_is_test_mode() ) {
		$secret_key = give_get_option( 'test_secret_key' );
	}

	return $secret_key;
}

/**
 * Get Custom FFM Fields.
 *
 * @param int $form_id     Donation Form ID.
 * @param int $donation_id Donation ID.
 *
 * @since 2.0.3
 *
 * @return array
 */
function give_stripe_get_custom_ffm_fields( $form_id, $donation_id = 0 ) {

	// Bail out, if FFM add-on is not active.
	if ( ! class_exists( 'Give_Form_Fields_Manager' ) ) {
		return array();
	}

	$ffm_meta     = array();
	$ffm_required = array();
	$ffm_optional = array();
	$field_label  = '';
	$ffm_fields   = give_get_meta( $form_id, 'give-form-fields', true );

	if ( is_array( $ffm_fields ) && count( $ffm_fields ) > 0 ) {

		// Loop through ffm fields.
		foreach ( $ffm_fields as $field ) {

			if ( $donation_id > 0 ) {
				$field_value = give_get_meta( $donation_id, $field['name'], true );
			} elseif ( ! empty( $_POST[ $field['name'] ] ) ) { // WPCS: input var ok, sanitization ok, CSRF ok.
				$field_value = give_clean( $_POST[ $field['name'] ] ); // WPCS: input var ok, sanitization ok, CSRF ok.
				$field_value = give_stripe_ffm_field_value_to_str( $field_value );

			} else {
				$field_value = __( '-- N/A --', 'give-stripe' );
			}

			// Strip the number of characters below 450 for custom fields value input when passed to metadata.
			if ( strlen( $field_value ) > 450 ) {
				$field_value = substr( $field_value, 0, 450 ) . '...';
			}

			if ( ! empty( $field['label'] ) ) {
				$field_label = strlen( $field['label'] ) > 25
					? trim( substr( $field['label'], 0, 25 ) ) . '...'
					: $field['label'];
			} elseif ( ! empty( $field['name'] ) ) {
				$field_label = strlen( $field['name'] ) > 25
					? trim( substr( $field['name'], 0, 25 ) ) . '...'
					: $field['name'];
			}

			// Make sure that the required fields are at the top.
			$required_field = ! empty( $field['required'] ) ? $field['required'] : '';
			if ( give_is_setting_enabled( $required_field ) ) {
				$ffm_required[ $field_label ] = is_array( $field_value ) ? implode( ' | ', $field_value ) : $field_value;
			} else {
				$ffm_optional[ $field_label ] = is_array( $field_value ) ? implode( ' | ', $field_value ) : $field_value;
			}

			$ffm_meta = array_merge( $ffm_required, $ffm_optional );

		} // End foreach().
	} // End if().

	return $ffm_meta;

}

/**
 * Get Preferred Locale based on the selection of language.
 *
 * @since 2.0.5
 *
 * @return string
 */
function give_stripe_get_preferred_locale() {

	$language_code = substr( get_locale(), 0, 2 ); // Get the lowercase language code. For Example, en, es, de.

	// Return "no" as accepted parameter for norwegian language code "nb" && "nn".
	$language_code = in_array( $language_code, array( 'nb', 'nn' ), true ) ? 'no' : $language_code;

	return apply_filters( 'give_stripe_elements_preferred_locale', $language_code );
}

/**
 * This function will record errors under Stripe Log.
 *
 * @param string $title   Log Title.
 * @param string $message Log Message.
 * @param int    $parent  Parent.
 *
 * @since 2.0.8
 *
 * @return int
 */
function give_stripe_record_log( $title = '', $message = '', $parent = 0 ) {
	$title = empty( $title ) ? esc_html__( 'Stripe Error', 'give-stripe' ) : $title;

	return give_record_log( $title, $message, $parent, 'stripe' );
}

/**
 * This function will be used to get Stripe transaction id link.
 *
 * @param int    $donation_id    Donation ID.
 * @param string $transaction_id Stripe Transaction ID.
 *
 * @since 2.0.8
 *
 * @return string
 */
function give_stripe_get_transaction_link( $donation_id, $transaction_id = '' ) {

	// If empty transaction id then get transaction id from donation id.
	if ( empty( $transaction_id ) ) {
		$transaction_id = give_get_payment_transaction_id( $donation_id );
	}

	$transaction_link = sprintf(
		'<a href="%1$s" target="_blank">%2$s</a>',
		give_stripe_get_transaction_url( $transaction_id ),
		$transaction_id
	);

	return $transaction_link;
}

/**
 * This function will return stripe transaction url.
 *
 * @param string $transaction_id Stripe Transaction ID.
 *
 * @since 2.0.8
 *
 * @return string
 */
function give_stripe_get_transaction_url( $transaction_id ) {

	$mode = '';

	if ( give_is_test_mode() ) {
		$mode = 'test/';
	}

	$transaction_url = esc_url_raw( "https://dashboard.stripe.com/{$mode}payments/{$transaction_id}" );

	return $transaction_url;
}

/**
 * This function will return connected account options.
 *
 * @since 2.1
 *
 * @return array
 */
function give_stripe_get_connected_account_options() {

	$options = array();

	if ( give_is_stripe_connected() ) {
		$options['stripe_account'] = give_get_option( 'give_stripe_user_id' );
	}

	return $options;
}

/**
 * This function will be used to convert upto 2 dimensional array to string as per FFM add-on Repeater field needs.
 *
 * This function is for internal purpose only.
 *
 * @param array|string $data Data to be converted to string.
 *
 * @since 2.1.1
 *
 * @return array|string
 */
function give_stripe_ffm_field_value_to_str( $data ) {

	if ( is_array( $data ) && count( $data ) > 0 ) {
		$count = 0;
		foreach ( $data as $item ) {
			if ( is_array( $item ) && count( $item ) > 0 ) {
				$data[ $count ] = implode( ',', $item );
			}

			$count ++;
		}

		$data = implode( '|', $data );
	}

	return $data;
}
