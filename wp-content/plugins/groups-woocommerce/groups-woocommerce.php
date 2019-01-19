<?php
/**
 * groups-woocommerce.php
 *
 * Copyright (c) 2012-2018 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package groups-woocommerce
 * @since groups-woocommerce 1.0.0
 *
 * Plugin Name: Groups WooCommerce
 * Plugin URI: http://www.itthinx.com/plugins/groups-woocommerce
 * Description: Memberships with Groups and WooCommerce. Integrates <a href="https://wordpress.org/plugins/groups/">Groups</a> with WooCommerce and WooCommerce Subscriptions for group membership management based on product purchases and subscriptions.
 * Version: 1.13.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * WC requires at least: 2.6
 * WC tested up to: 3.5
 * Woo: 18704:aa2d455ed00566e4fb71bc9d53f2613b
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GROUPS_WS_VERSION', '1.13.0' );

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'aa2d455ed00566e4fb71bc9d53f2613b', '18704' );

function groups_woocommerce_plugins_loaded() {
	if ( is_woocommerce_active() ) {
		define( 'GROUPS_WS_FILE', __FILE__ );
		define( 'GROUPS_WS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'GROUPS_WS_PLUGIN_DOMAIN', 'groups-woocommerce' );
		if ( !defined( 'GROUPS_WS_LOG' ) ) {
			define( 'GROUPS_WS_LOG', false );
		}
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0' ) >= 0 ) {
			$lib = '/lib';
		} else {
			$lib = '/lib-2';
		}
		if ( !defined( 'GROUPS_WS_CORE_DIR' ) ) {
			define( 'GROUPS_WS_CORE_DIR', WP_PLUGIN_DIR . '/groups-woocommerce' );
		}
		if ( !defined( 'GROUPS_WS_CORE_LIB' ) ) {
			define( 'GROUPS_WS_CORE_LIB', GROUPS_WS_CORE_DIR . $lib . '/core' );
		}
		if ( !defined( 'GROUPS_WS_ADMIN_LIB' ) ) {
			define( 'GROUPS_WS_ADMIN_LIB', GROUPS_WS_CORE_DIR . $lib . '/admin' );
		}
		if ( !defined( 'GROUPS_WS_VIEWS_LIB' ) ) {
			define( 'GROUPS_WS_VIEWS_LIB', GROUPS_WS_CORE_DIR . $lib . '/views' );
		}
		require_once( GROUPS_WS_CORE_LIB . '/class-groups-ws.php');
	}
}
add_action( 'plugins_loaded', 'groups_woocommerce_plugins_loaded' );

/**
 * Adds links to documentation and support to the plugin's row meta.
 *
 * @param array $plugin_meta plugin row meta entries
 * @param string $plugin_file path to the plugin file - relative to the plugins directory
 * @param string $plugin_data plugin data entries
 * @param string $status current status of the plugin
 *
 * @return array[string]
 */
function group_woocommerce_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if ( $plugin_file == plugin_basename( GROUPS_WS_FILE ) ) {
		$plugin_meta[] = '<a href="https://woocommerce.com/products/groups-woocommerce/">' . esc_html__( 'Extension', 'groups-woocommerce' ) . '</a>';
		$plugin_meta[] = '<a style="font-weight:bold" href="http://docs.woothemes.com/document/groups-woocommerce/">' . esc_html__( 'Documentation', 'groups-woocommerce' ) . '</a>';
		$plugin_meta[] = '<a style="font-weight:bold" href="https://woocommerce.com/my-account/create-a-ticket/">' . esc_html__( 'Support', 'groups-woocommerce' ) . '</a>';
	}
	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'group_woocommerce_plugin_row_meta', 10, 4 );
