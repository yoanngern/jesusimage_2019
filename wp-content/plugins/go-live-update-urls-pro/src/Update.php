<?php

/**
 * Update the plugin from the api.
 *
 * @author Mat Lipe
 * @since  2.5.0
 *
 * @notice Special `init()` pattern to prevent extending or removing actions.
 */
final class Go_Live_Update_URLS_Pro_Update {
	const VERSION = '2.2.0';

	/**
	 * Root directory of this plugin.
	 *
	 * @var string;
	 */
	private $root;

	/**
	 * Slug of this plugin.
	 *
	 * @var string
	 */
	private $slug;


	/**
	 * Create the instance of the class.
	 *
	 * @param string $slug - Plugin slug.
	 * @param string $root - Plugin root directory.
	 */
	public function __construct( $slug, $root ) {
		$this->slug = $slug;
		$this->root = $root;
	}


	/**
	 * Add actions and filters.
	 */
	private function hook() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 10, 3 );
		add_action( "after_plugin_row_{$this->slug}/{$this->slug}.php", array(
			$this,
			'invalid_license_row',
		), 9, 2 );
		add_action( 'all_admin_notices', array( $this, 'invalid_license_notice' ) );

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
		if ( defined( 'LIPE_PLUGIN_API_CHECK_VERSION' ) ) {
			return LIPE_PLUGIN_API_CHECK_VERSION;
		}

		if ( ! empty( $plugins->checked[ $this->slug . '/' . $this->slug . '.php' ] ) ) {
			return $plugins->checked[ $this->slug . '/' . $this->slug . '.php' ];
		}

		return null;
	}


	/**
	 * Display an admin notice if the current license is invalid.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function invalid_license_notice() {
		$plugins = get_site_transient( 'update_plugins' );
		if ( isset( $plugins->response[ $this->slug . '/' . $this->slug . '.php' ]->invalid_license ) ) {
			?>
			<div class="error">
				<p>
					<?php echo $plugins->response[ $this->slug . '/' . $this->slug . '.php' ]->invalid_license; //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</p>
			</div>
			<?php
		}
	}


	/**
	 * If the license is invalid this displays a notice in the plugins list.
	 *
	 * @param string $plugin_file - Slug and main plugin file.
	 * @param array  $plugin_data - Accumulated data from plugin file and endpoint.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function invalid_license_row( $plugin_file, $plugin_data ) {
		if ( isset( $plugin_data['invalid_license'] ) ) {
			// Remove default update message.
			remove_action( "after_plugin_row_{$plugin_file}", 'wp_plugin_update_row' );

			if ( is_network_admin() ) {
				$active_class = is_plugin_active_for_network( $plugin_file ) ? ' active' : '';
			} else {
				$active_class = is_plugin_active( $plugin_file ) ? ' active' : '';
			}
			?>
			<tr class="plugin-update-tr<?php echo esc_attr( $active_class ); ?>">
				<td colspan="3" class="plugin-update">
					<div class="update-message notice inline notice-error">
						<p>
							<?php echo $plugin_data['invalid_license']; //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</p>
					</div>
				</td>
			</tr>
			<?php

		}
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
			$plugins->checked[ $this->slug . '/' . $this->slug . '.php' ] = $version;
			$args                                                         = array(
				'slug'    => $this->slug,
				'version' => $version,
			);
			$response                                                     = $this->do_request( $args, 'basic_check' );

			if ( ! empty( $response ) && is_object( $response ) ) {
				$plugins->response[ $this->slug . '/' . $this->slug . '.php' ] = $response;
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
		if ( empty( $args->slug ) || $this->slug !== $args->slug ) {
			return $info;
		}

		$plugin_info   = get_site_transient( 'update_plugins' );
		$args->version = $plugin_info->checked[ $this->slug . '/' . $this->slug . '.php' ];

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
		$license = include $this->root . '/LICENSE.php';
		if ( defined( 'LIPE_PLUGIN_API_LICENSE' ) ) {
			$license['hash'] = LIPE_PLUGIN_API_LICENSE;
		}

		return $license;
	}


	/**
	 * Retrieve the url of the update api.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	private function get_api_url() {
		if ( defined( 'LIPE_PLUGIN_API_URL' ) ) {
			return LIPE_PLUGIN_API_URL;
		}

		// Must use http until forced PHP version 5.5+ or curl will have tls error.
		return 'http://matlipe.com/plugins/v2';
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
		$raw_response = wp_remote_post( $this->get_api_url(), $request );
		if ( is_wp_error( $raw_response ) || ( 200 !== (int) $raw_response['response']['code'] ) ) {
			//phpcs:ignore WordPress.WP.I18n.MissingArgDomain
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
	 * Create the instance of the class.
	 *
	 * @return void
	 */
	public function init() {
		$this->hook();
	}

}
