<?php
/**
 * Plugin Name: Give - Stripe Gateway
 * Plugin URI:  https://givewp.com/addons/stripe-gateway/
 * Description: Adds the Stripe.com payment gateway to the available Give payment methods.
 * Version:     2.1.8
 * Author:      GiveWP
 * Author URI:  https://givewp.com/
 * Text Domain: give-stripe
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 *
 * Required minimum versions, paths, urls, etc.
 */
if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
	define( 'GIVE_STRIPE_VERSION', '2.1.8' );
}
if ( ! defined( 'GIVE_STRIPE_MIN_GIVE_VER' ) ) {
	define( 'GIVE_STRIPE_MIN_GIVE_VER', '2.3.0' );
}
if ( ! defined( 'GIVE_STRIPE_MIN_PHP_VER' ) ) {
	define( 'GIVE_STRIPE_MIN_PHP_VER', '5.3.0' );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_FILE' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_DIR' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_DIR', dirname( GIVE_STRIPE_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_URL' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_URL', plugin_dir_url( GIVE_STRIPE_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_STRIPE_BASENAME' ) ) {
	define( 'GIVE_STRIPE_BASENAME', plugin_basename( GIVE_STRIPE_PLUGIN_FILE ) );
}


if ( ! class_exists( 'Give_Stripe' ) ) :

	/**
	 * Class Give_Stripe.
	 */
	class Give_Stripe {

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var Give_Stripe
		 */
		private static $instance;

		/**
		 * Stripe Add-on Upgrades.
		 *
		 * @var Give_Stripe_Upgrades.
		 */
		public $upgrades;

		/**
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Give_Stripe The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Give Stripe.
		 *
		 * @since  2.1.1
		 * @access private
		 */
		private function setup() {
			// Give init hook.
			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 *
		 * @since 2.1.1
		 */
		public function init() {

			$this->licensing();
			load_plugin_textdomain( 'give-stripe', false, dirname( GIVE_STRIPE_BASENAME ) . '/languages' );

			// Don't hook anything else in the plugin if we're in an incompatible environment.
			if ( ! $this->get_environment_warning() ) {
				return;
			}

			$this->activation_banner();

			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
			add_filter( 'give_payment_statuses', array( $this, 'payment_status_labels' ) );

			$this->includes();

		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @param string $slug Notice Slug.
		 * @param string $class Notice Class.
		 * @param string $message Notice Message.
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Display admin notices.
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

		}

		/**
		 * Give Stripe Includes.
		 */
		private function includes() {

			// Stripe PHP library.
			require_once GIVE_STRIPE_PLUGIN_DIR . '/vendor/autoload.php';

			if ( is_admin() ) {
				include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-activation.php' );
				include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-upgrades.php' );
				include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-admin.php' );
				include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-settings.php' );
				include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/class-give-stripe-apple-pay-registration.php' );
			}

			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/class-give-stripe-logger.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/give-stripe-connect.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/deprecated/deprecated-functions.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/give-stripe-helpers.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/give-stripe-scripts.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/class-give-stripe-customer.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/class-give-stripe-gateway.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/give-stripe-preapproval.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-card.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-ach.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/payment-methods/class-give-stripe-ideal.php' );
			include( GIVE_STRIPE_PLUGIN_DIR . '/includes/class-give-stripe-email-tags.php' );

		}

		/**
		 * Register the Stripe payment gateways.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			// Format: ID => Name.
			$gateways['stripe']     = array(
				'admin_label'    => __( 'Stripe - Credit Card', 'give-stripe' ),
				'checkout_label' => __( 'Credit Card', 'give-stripe' ),
			);
			$gateways['stripe_ach'] = array(
				'admin_label'    => __( 'Stripe + Plaid', 'give-stripe' ),
				'checkout_label' => __( 'Bank Account', 'give-stripe' ),
			);

			return $gateways;
		}

		/**
		 * Plugin Licensing.
		 */
		public function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( GIVE_STRIPE_PLUGIN_FILE, 'Stripe Gateway', GIVE_STRIPE_VERSION, 'WordImpress', 'stripe_license_key' );
			}
		}

		/**
		 * Register our new payment status labels for Give Stripe.
		 *
		 * @since 1.0
		 *
		 * @param array $statuses List of post status.
		 *
		 * @return mixed
		 */
		public function payment_status_labels( $statuses ) {
			$statuses['preapproval'] = __( 'Preapproved', 'give-stripe' );

			return $statuses;
		}

		/**
		 * Check plugin environment.
		 *
		 * @since  2.1.1
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Load plugin helper functions.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - Stripe to activate.', 'give-stripe' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since  2.1.1
		 * @access public
		 *
		 * @return bool
		 */
		public function get_environment_warning() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Verify dependency cases.
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_STRIPE_MIN_GIVE_VER, '<' )
			) {

				/* Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - Stripe add-on to activate.', 'give-stripe' ), 'https://givewp.com', GIVE_STRIPE_MIN_GIVE_VER ) );

				$is_working = false;
			}

			if ( version_compare( phpversion(), GIVE_STRIPE_MIN_PHP_VER, '<' ) ) {
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">PHP</a> version %s or above for the Give - Stripe gateway add-on to activate.', 'give-stripe' ), 'https://givewp.com/documentation/core/requirements/', GIVE_STRIPE_MIN_PHP_VER ) );

				$is_working = false;
			}

			if ( ! function_exists( 'curl_init' ) ) {
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">cURL</a> installed for the Give - Stripe gateway add-on to activate.', 'give-stripe' ), 'https://givewp.com/documentation/core/requirements/' ) );

				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Give Stripe activation banner.
		 *
		 * Includes and initializes Give activation banner class.
		 *
		 * @since 2.1.1
		 */
		public function activation_banner() {

			// Check for activation banner inclusion.
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				// Only runs on admin.
				$args = array(
					'file'              => GIVE_STRIPE_PLUGIN_FILE,
					'name'              => esc_html__( 'Stripe Gateway', 'give-stripe' ),
					'version'           => GIVE_STRIPE_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ),
					'documentation_url' => 'http://docs.givewp.com/addon-stripe',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false
				);

				new Give_Addon_Activation_Banner( $args );

			}

			return true;

		}

	}

	$GLOBALS['give_stripe'] = Give_Stripe::get_instance();

endif; // End if class_exists check.
