<?php

/**
 * Go_Live_Update_URLS_Pro_Core
 *
 * @author mat
 * @since  2.2.0
 *
 */
class Go_Live_Update_URLS_Pro_Core {

	/**
	 * checkboxes
	 *
	 * @var Go_Live_Update_URLS_Pro_Checkboxes $checkboxes
	 */
	private $checkboxes;

	/**
	 * Used along with self::plugin_path() to return path to this plugins files
	 *
	 * @var string
	 */
	private static $plugin_path = false;

	/**
	 * To keep track of this plugins root dir
	 * Used along with self::plugin_url() to return url to plugin files
	 *
	 * @var string
	 */
	public static $plugin_url;


	private function __construct() {
		$this->checkboxes = new Go_Live_Update_URLS_Pro_Checkboxes();
	}


	private function hook() {
		add_filter( 'gluu-uncheck-message', array( $this, 'check_message' ) );

		add_filter( 'go-live-update-urls-serialized-tables', array( $this, 'add_serialized_tables' ) );
		add_filter( 'go-live-update-urls-success-message', array( $this, 'success_message' ) );

		add_action( 'gluu_after_checkboxes', array( $this, 'checkboxes' ) );
		add_filter( 'gluu-use-default_checkboxes', '__return_false' );
		add_action( 'gluu_before_checkboxes', array( $this, 'safe_to_update_message' ) );

		add_filter( 'go-live-update-urls/database/update-tables', array( $this, 'swap_tables' ) );
	}


	public function get_old_url() {
		$predicted = Go_Live_Update_URLS_Pro__History__Tracking::instance()->get_predicted_urls();
		$value     = '';
		if ( ! empty( $predicted['old'] ) ) {
			$value = $predicted['old'];
		}

		return apply_filters( 'go-live-update-urls-pro/core/old-url', $value );
	}


	public function get_new_url() {
		$predicted = Go_Live_Update_URLS_Pro__History__Tracking::instance()->get_predicted_urls();
		$value     = '';
		if ( ! empty( $predicted['new'] ) ) {
			$value = $predicted['new'];
		}

		return apply_filters( 'go-live-update-urls-pro/core/new-url', $value );
	}


	public function success_message() {
		return __( 'The URLS in the checked sections have been updated.', 'go-live-update-urls' );
	}


	public function safe_to_update_message() {
		?>
		<div id="message" class="updated notice notice-success is-dismissible">
			<p>
				<?php echo esc_html( $this->top_message() ); ?>
			</p>
		</div>
		<?php
	}


	/**
	 * add_serialized_tables
	 *
	 * Go through the custom tables, pull out the serialized ones and
	 * add them to the array so we update them properly
	 *
	 * Do nothing is we do not have custom checked
	 *
	 * @param $tables
	 *
	 * @return array
	 */
	public function add_serialized_tables( $tables ) {
		$custom = $this->checkboxes->custom();

		$serial = new Go_Live_Update_URLS_Pro_Serialized_Tables( $custom->tables, $tables );

		return $serial->get_tables();
	}


	/**
	 *
	 * Convert tables to standard values
	 * This will receive a list of table categories
	 * We need table names
	 *
	 * @param array $tables
	 *
	 * @return array
	 */
	public function swap_tables( $tables ) {
		return $this->checkboxes->swap_tables( $tables );
	}


	public function checkboxes() {
		$this->checkboxes->render();
	}


	public function check_message() {
		return __( 'Only the checked sections will be updated.', 'go-live-update-urls' );
	}


	public function top_message() {
		return __( 'All tables are safe to update.', 'go-live-update-urls' );
	}


	/**
	 * Retrieve the path this plugins dir
	 *
	 * @param string [$append] - optional path file or name to add
	 *
	 * @return string
	 */
	public static function plugin_path( $append = '' ) {
		if ( ! self::$plugin_path ) {
			self::$plugin_path = trailingslashit( dirname( dirname( __FILE__ ) ) );
		}

		return trailingslashit( self::$plugin_path . $append );
	}


	/**
	 * Retrieve the url this plugins dir
	 *
	 * @param string [$append] - optional path file or name to add
	 *
	 * @return string
	 */
	public static function plugin_url( $append = '' ) {
		if ( ! self::$plugin_url ) {
			self::$plugin_url = trailingslashit( plugins_url( basename( self::plugin_path() ), dirname( dirname( __FILE__ ) ) ) );
		}

		return trailingslashit( self::$plugin_url . $append );
	}

	//********** SINGLETON FUNCTIONS **********/


	/**
	 * Instance of this class for use as singleton
	 *
	 * @var Go_Live_Update_URLS_Pro_Core
	 */
	private static $instance;


	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::instance()->hook();
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the
	 * class
	 *
	 * @static
	 * @return Go_Live_Update_Urls_Pro_Core
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
