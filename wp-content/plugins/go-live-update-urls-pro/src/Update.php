<?php
//define('CHECK_VERSION', true);


/**
 * Main Update Class
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 */
final class Go_Live_Update_URLS_Pro_Update {
	public $api_url = 'http://matlipe.com/plugins/'; //must use http: because PHP 5.2 does not support tlsv1.2 which is the only thing the server supports

	public $plugin_slug = 'go-live-update-urls-pro';


	private function hook() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_api_call' ), 10, 3 );

		if ( defined( 'CHECK_VERSION' ) ) {
			set_site_transient( 'update_plugins', null );
		}
	}


	/**
	 * Checks our custom location for an available update
	 *
	 * @uses added to the pre_set_site_transient_update_plugins filter by self::__construct();
	 */
	public function check_for_update( $checked_data ) {
		global $wp_version;
		//Comment out these two lines during testing.
		if ( empty( $checked_data->checked[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ] ) ) {
			return $checked_data;
		}

		$args           = array(
			'slug'    => $this->plugin_slug,
			'version' => $checked_data->checked[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ],
		);
		$request_string = array(
			'body'       => array(
				'action'  => 'basic_check',
				'request' => serialize( $args ),
				'api-key' => md5( get_bloginfo( 'url' ) ),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
		);

		// Start checking for an update
		$raw_response = wp_remote_post( $this->api_url, $request_string );
		if ( ! is_wp_error( $raw_response ) && ( 200 === (int) $raw_response['response']['code'] ) ) {
			$response = unserialize( $raw_response['body'] );
		}

		if ( ! empty( $response ) && is_object( $response ) ) {
			// Feed the update data into WP updater
			$checked_data->response[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ] = $response;
		}

		return $checked_data;
	}


	/**
	 * switch the api call to the custom location
	 *
	 * @since 1.0
	 *
	 * @uses  added to the 'plugins_api' filter by self::__construct()
	 */
	public function plugin_api_call( $def, $action, $args ) {
		global $wp_version;
		if ( empty( $this->plugin_slug ) || empty( $args->slug ) || $args->slug !== $this->plugin_slug ) {
			return false;
		}

		// Get the current version
		$plugin_info     = get_site_transient( 'update_plugins' );
		$current_version = $plugin_info->checked[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ];
		$args->version   = $current_version;

		$request_string = array(
			'body'       => array(
				'action'  => $action,
				'request' => serialize( $args ),
				'api-key' => md5( get_bloginfo( 'url' ) ),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
		);

		$request = wp_remote_post( $this->api_url, $request_string );

		if ( is_wp_error( $request ) ) {
			$res = new WP_Error( 'plugins_api_failed', '<p>' . __( 'An Unexpected HTTP Error occurred during the API request.', 'go-live-update-urls' ) . '</p> <p><a href="?" onclick="document.location.reload(); return false;">' . __( 'Try again', 'go-live-update-urls' ) . '</a>', $request->get_error_message() );
		} else {
			$res = unserialize( $request['body'] );

			if ( false === $res ) {
				$res = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred', 'go-live-update-urls' ), $request['body'] );
			}
		}

		return $res;
	}

	//********** SINGLETON **********/


	/**
	 * Instance of this class for use as singleton
	 *
	 * @var self
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
	 * @return self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
