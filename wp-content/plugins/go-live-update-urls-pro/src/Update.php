<?php

/**
 * Update the plugin from the api.
 *
 * @todo Convert this class to universal use by allowing passing of slug and root.
 *       Will have to rename it to something universal.
 *
 * @author Mat Lipe
 * @since  2.5.0
 */
final class Go_Live_Update_URLS_Pro_Update {
	const PLUGIN_SLUG = 'go-live-update-urls-pro';
	const ROOT        = GO_LIVE_UPDATE_URLS_PRO_DIR;

	const VERSION = '2.1.0';
	const API_URL = 'http://matlipe.com/plugins/v2'; // Must use http: because PHP 5.2 does not support tlsv1.2 which is the only thing the server supports.


	/**
	 * Add actions and filters.
	 */
	private function hook() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_update' ] );
		add_filter( 'plugins_api', [ $this, 'get_plugin_info' ], 10, 3 );

		if ( defined( 'LIPE_PLUGIN_API_CHECK_VERSION' ) ) {
			set_site_transient( 'update_plugins', null );
		}
	}


	/**
	 * Get the current version of this plugin.
	 *
	 * @since 2.1.0
	 *
	 * @param object $plugins - WordPress data about plugins and versions.
	 *
	 * @return mixed|null
	 */
	private function get_current_version( $plugins ) {
		if ( ! empty( $plugins->checked[ self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' ] ) ) {
			return $plugins->checked[ self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' ];
		}
		if ( defined( 'LIPE_PLUGIN_API_CHECK_VERSION' ) ) {
			return LIPE_PLUGIN_API_CHECK_VERSION;
		}

		return null;
	}


	/**
	 * Checks our custom location for an available update
	 *
	 * @param object $plugins - Date about all the current plugins and their version.
	 *
	 * @return mixed
	 */
	public function check_for_update( $plugins ) {
		$version = $this->get_current_version( $plugins );
		if ( null !== $version ) {
			$plugins->checked[ self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' ] = $version;
			$args                                                                     = array(
				'slug'    => self::PLUGIN_SLUG,
				'version' => $version,
			);
			$response                                                                 = $this->do_request( $args, 'basic_check' );

			if ( ! empty( $response ) && is_object( $response ) ) {
				$plugins->response[ self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' ] = $response;
			}
		}

		return $plugins;
	}

	/**
	 * Point any plugin api calls which match this plugin's slug to our custom endpoint.
	 *
	 * @param mixed  $info   - Typically this is just `false` if not filtered.
	 * @param string $action - Action we are calling.
	 * @param object $args   - Plugin arguments.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function get_plugin_info( $info, $action, $args ) {
		if ( empty( $args->slug ) || self::PLUGIN_SLUG !== $args->slug ) {
			return $info;
		}

		$plugin_info   = get_site_transient( 'update_plugins' );
		$args->version = $plugin_info->checked[ self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' ];

		return $this->do_request( (array) $args, $action );
	}


	/**
	 * Retrieve the license info from the `LICENSE.php` file.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_license() {
		return include self::ROOT . '/LICENSE.php';
	}


	/**
	 * Make a POST request to the plugin api endpoint.
	 *
	 * @param array  $args   - Arguments to pass as the `request` parameter.
	 * @param string $action - The action we are executing.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed|WP_Error
	 */
	private function do_request( $args, $action ) {
		$request      = array(
			'body'       => array(
				'action'           => $action,
				'args'             => wp_json_encode( $args ),
				'origin'           => get_bloginfo( 'url' ),
				'license'          => $this->get_license(),
				'endpoint_version' => self::VERSION,
			),
			'user-agent' => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . get_bloginfo( 'url' ),
		);
		$raw_response = wp_remote_post( self::API_URL, $request );
		if ( is_wp_error( $raw_response ) || ( 200 !== (int) $raw_response['response']['code'] ) ) {
			return new WP_Error( 'plugins_api_failed', '<p>' . __( 'An Unexpected HTTP Error occurred during the API request.' ) . '</p> <p><a href="?" onclick="document.location.reload(); return false;">' . __( 'Try again' ) . '</a>', $raw_response->get_error_message() );
		}

		$response = json_decode( $raw_response['body'], false );
		// Sections must be an array.
		if ( isset( $response->sections ) ) {
			$response->sections = (array) $response->sections;
		}

		return $response;

	}


	/**
	 * Instance of this class for use as singleton
	 *
	 * @var Advanced_Sidebar_Menu_Pro_Update
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
