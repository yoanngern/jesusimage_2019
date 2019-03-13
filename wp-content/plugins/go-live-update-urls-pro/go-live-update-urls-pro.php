<?php
/**
 * Plugin Name: Go Live Update URLS Pro
 * Plugin URI: https://matlipe.com/go-live-update-urls-pro/
 * Description: Make Go Live Update URLS smarter and easier to use.
 * Author: Mat Lipe
 * Author URI: https://matlipe.com/
 * Version: 2.4.0
 * Text Domain: go-live-update-urls
 *
 * @package go-live-update-urls-pro
 */

define( 'GO_LIVE_UPDATE_URLS_PRO_VERSION', '2.4.0' );
define( 'GO_LIVE_UPDATE_URLS_LAST_VERSION', '2.3.3' );
define( 'GO_LIVE_UPDATE_URLS_REQUIRED_BASIC_VERSION', '5.2.5' );

/**
 * Load the plugin
 *
 * @return void
 */
function go_live_update_urls_pro_load() {
	if ( ! defined( 'WP_TESTS_DIR' ) && ! is_admin() ) {
		return;
	}

	load_plugin_textdomain( 'go-live-update-urls', false, 'go-live-update-urls-pro/languages' );

	if ( ! defined( 'GO_LIVE_UPDATE_URLS_VERSION' ) || version_compare( GO_LIVE_UPDATE_URLS_REQUIRED_BASIC_VERSION, GO_LIVE_UPDATE_URLS_VERSION, '>' ) ) {
		add_action( 'admin_notices', 'go_live_update_urls_pro_base_plugin_notice' );

		return;
	}

	Go_Live_Update_URLS_Pro_Styles::init();
	Go_Live_Update_URLS_Pro__History__Ajax::init();
	Go_Live_Update_URLS_Pro__History__Tracking::init();
	Go_Live_Update_URLS_Pro_Tests_Ajax::init();
	Go_Live_Update_URLS_Pro_Core::init();
	Go_Live_Update_URLS_Pro_Update::init();
}

add_action( 'plugins_loaded', 'go_live_update_urls_pro_load', 9 );

/**
 * Autoload classes from PSR4 src directory
 * Mirrored after Composer dump-autoload for performance
 *
 * @param string $class - class to load.
 *
 * @return void
 */
function go_live_update_urls_pro_autoload( $class ) {
	$classes = array(
		// core.
		'Go_Live_Update_URLS_Pro_Checkboxes'           => 'Checkboxes.php',
		'Go_Live_Update_URLS_Pro_Core'                 => 'Core.php',
		'Go_Live_Update_URLS_Pro_Serialized_Tables'    => 'Serialized_Tables.php',
		'Go_Live_Update_URLS_Pro_Styles'               => 'Styles.php',
		'Go_Live_Update_URLS_Pro_Update'               => 'Update.php',

		// history.
		'Go_Live_Update_URLS_Pro__History__Ajax'       => 'History/Ajax.php',
		'Go_Live_Update_URLS_Pro__History__Tracking'   => 'History/Tracking.php',

		// tests .
		'Go_Live_Update_URLS_Pro_Tests_Abstract'       => 'Tests/Abstract.php',
		'Go_Live_Update_URLS_Pro_Tests_Ajax'           => 'Tests/Ajax.php',
		'Go_Live_Update_URLS_Pro_Tests_Domain'         => 'Tests/Domain.php',
		'Go_Live_Update_URLS_Pro_Tests_Repo'           => 'Tests/Repo.php',
		'Go_Live_Update_URLS_Pro_Tests_Scheme'         => 'Tests/Scheme.php',
		'Go_Live_Update_URLS_Pro_Tests_Trailing_Slash' => 'Tests/Trailing_Slash.php',

	);
	if ( isset( $classes[ $class ] ) ) {
		require dirname( __FILE__ ) . '/src/' . $classes[ $class ];
	}
}

spl_autoload_register( 'go_live_update_urls_pro_autoload' );


/**
 * Display a warning if we don't have the required basic version installed
 *
 * @return void
 */
function go_live_update_urls_pro_base_plugin_notice() {
	?>
	<div id="message" class="error">
		<p>
			<?php
			/* translators: {%1$s}[<a>]{%2$s}[</a>] https://wordpress.org/plugins/go-live-update-urls/ */
			printf( esc_html_x( 'Go Live Update Urls Pro requires the basic version of %1$sGo Live Update Urls %3$s+%2$s to be installed and active.', '{<a>}{</a>}', 'go-live-update-urls' ), '<a target="_blank" href="https://wordpress.org/plugins/go-live-update-urls/">', '</a>', esc_attr( GO_LIVE_UPDATE_URLS_REQUIRED_BASIC_VERSION ) );
			?>
		</p>
	</div>
	<?php
}
