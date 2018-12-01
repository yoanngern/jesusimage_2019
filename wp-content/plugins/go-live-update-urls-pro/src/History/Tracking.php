<?php

/**
 * Go_Live_Update_URLS_Pro__History__Tracking
 *
 * @author Mat Lipe
 * @since  2.2.0
 *
 */
class Go_Live_Update_URLS_Pro__History__Tracking {
	const OPTION = 'go-live-update-urls-pro/history/tracking/changes';
	const LIMIT = 10;

	protected $history_to_restore;


	protected function hook() {
		add_action( 'update_option_home', array( $this, 'track_site_url_change' ), 10, 2 );
		add_action( 'go-live-update-urls/database/before-update', array(
			$this,
			'track_site_url_change_from_go_live',
		), 10, 3 );
	}


	/**
	 * Get the last change of old to new urls.
	 *
	 * If this change occurred more than 1 day ago
	 * return nothing.
	 *
	 * @return mixed|null
	 */
	public function get_predicted_urls() {
		$history = $this->get_history();
		if ( empty( $history ) ) {
			return null;
		}
		$latest    = end( $history );
		$yesterday = strtotime( '-1 day' );

		if ( $latest['date'] > $yesterday ) {
			return $latest;
		}

		return null;
	}


	/**
	 * Add an url change to the history when updating the options
	 * table via Go Live Update URLS
	 *
	 * @param string $old_url
	 * @param string $new_url
	 * @param array  $tables
	 *
	 * @since 2.2.1
	 *
	 * @return void
	 */
	public function track_site_url_change_from_go_live( $old_url, $new_url, array $tables ) {
		global $wpdb;
		if ( in_array( Go_Live_Update_URLS_Pro_Checkboxes::OPTIONS, $tables, true ) || in_array( $wpdb->options, $tables, true ) ) {
			$this->track_site_url_change( $old_url, $new_url );

			$this->history_to_restore = $this->get_history();
			add_action( 'go-live-update-urls/database/after-update', array( $this, 'restore_history' ), 10, 0 );
		}

	}


	/**
	 * The main Go Live process will replace values in history as well.
	 * Therefore we track it before the update and replace it afterward.
	 *
	 * @see    Go_Live_Update_URLS_Pro__History__Tracking::track_site_url_change_from_go_live()
	 *
	 * @action 'go-live-update-urls/database/after-update'
	 *
	 * @since  2.2.1
	 *
	 * @return void
	 */
	public function restore_history() {
		$this->save( $this->history_to_restore );
	}


	/**
	 * Track the change in urls
	 *
	 * @param string $old_url
	 * @param string $new_url
	 *
	 * @return void
	 */
	public function track_site_url_change( $old_url, $new_url ) {
		$history         = $this->get_history();
		$new_entry       = array(
			'old'  => $old_url,
			'new'  => $new_url,
			'date' => current_time( 'timestamp' ),
		);
		$new_entry['id'] = wp_hash( wp_json_encode( $new_entry ) );

		$history[] = $new_entry;
		$this->save( $history );

	}


	public function get_tracking_limit() {
		return (int) apply_filters( 'go-live-update-urls-pro/history/tracking/limit', self::LIMIT );
	}


	/**
	 *
	 * @return array
	 */
	public function get_history() {
		return get_option( self::OPTION, array() );
	}


	protected function save( $history ) {
		$limit = $this->get_tracking_limit();
		update_option( self::OPTION, array_slice( $history, - $limit, $limit ), false );
	}


	//********** SINGLETON **********/


	/**
	 * Instance of this class for use as singleton
	 *
	 * @var self
	 */
	protected static $instance;


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
	 * @return self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
