<?php

/**
 * Go_Live_Update_URLS_Pro__History__Ajax
 *
 * @author Mat Lipe
 * @since  2.2.0
 *
 */
class Go_Live_Update_URLS_Pro__History__Ajax {
	const GET_HISTORY = 'go-live-update-urls-pro/test/get-history';


	private function hook() {
		add_action( 'wp_ajax_' . self::GET_HISTORY, array( $this, 'ajax_get_history' ) );
	}


	public function ajax_get_history() {
		$history = Go_Live_Update_URLS_Pro__History__Tracking::instance()->get_history();
		foreach ( $history as $k => $change ) {
			$history[ $k ]['date'] = date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), (int) $change['date'] );
		}

		wp_send_json_success( array_reverse( $history ) );
	}

	//********** SINGLETON **********/


	/**
	 * Instance of this class for use as singleton
	 *
	 * @param Go_Live_Update_URLS_Pro__History__Ajax
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
	 * @return Go_Live_Update_URLS_Pro__History__Ajax
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
