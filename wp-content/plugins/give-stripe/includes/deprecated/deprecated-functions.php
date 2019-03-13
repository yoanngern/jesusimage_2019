<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Give-Stripe
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet.
 *
 * @since  1.4
 * @deprecated 2.1
 *
 * @param  int $user_id_or_email The user ID or email to look up.
 *
 * @return string       Stripe customer ID.
 */
function give_get_stripe_customer_id( $user_id_or_email ) {
	return give_stripe_get_customer_id( $user_id_or_email );
}

