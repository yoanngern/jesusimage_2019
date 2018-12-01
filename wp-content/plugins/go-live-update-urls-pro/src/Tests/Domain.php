<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Domain
 *
 * @author Mat Lipe
 * @since  2.0.0
 *
 */
class Go_Live_Update_URLS_Pro_Tests_Domain extends Go_Live_Update_URLS_Pro_Tests_Abstract {
	public $messages = array();


	protected function __construct() {
		$this->messages = apply_filters( 'go-live-update-urls-pro/tests/domain-messages', array(
			'warning' => esc_html__( 'Unable to verify whether or not this site is accessible from the new url.', 'go-live-update-urls' ),
			'fail'    => esc_html__( 'This site is not accessible when the Old URL is replaced with the New URL.', 'go-live-update-urls' ),
			'pass'    => esc_html__( 'This site is accessible from the New URL.', 'go-live-update-urls' ),
		) );
	}


	/**
	 * 1. Is the new url the current site url or domain ->  pass
	 * 2. Is the old url NOT the current site url or domain -> unknown
	 * 3. Is the old url NOT found somewhere in the site url -> unknown
	 * 4. Is the site accessible from the request url with old url replaced with new -> pass
	 *
	 * @return void
	 */
	protected function test() {
		$this->result = true;

		$site_url = site_url();
		$parts    = wp_parse_url( $site_url );
		//site_url() will not have a slash
		$new_url = untrailingslashit( $this->new_url );
		$old_url = untrailingslashit( $this->old_url );

		/**
		 * If the new url is the current site url or domain we have nothing to
		 * replace in the $post_url and therefore must stop testing here.
		 */
		if ( $new_url === $site_url || $new_url === $parts['host'] ) {
			return;
		}

		/**
		 * If the old url is not the site url or domain then neither the new
		 * nor old url is something we can test against so we are unknown
		 */
		if ( $old_url !== $site_url && $old_url !== $parts['host'] ) {
			$this->result = 'unknown';

			return;
		}

		$key = wp_hash( time() );
		update_option( Go_Live_Update_URLS_Pro_Tests_Ajax::DOMAIN_KEY, $key );

		$_args = array(
			'body'      => array(
				Go_Live_Update_URLS_Pro_Tests_Ajax::DOMAIN_KEY => site_url(),

				'action' => Go_Live_Update_URLS_Pro_Tests_Ajax::VERIFY_DOMAIN,
				'hash'   => md5( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'endpoint' . DIRECTORY_SEPARATOR . 'domain-test-endpoint.php' ),
			),
			'sslverify' => false,
			'headers'   => array( 'Accept' => 'application/json' ),
		);

		if ( ! is_multisite() ) {
			$post_url = admin_url( 'admin-post.php' );
		} else {
			//multisite redirects the requests which make ajax always fail.
			//We use our own endpoint when testing multisite.
			$post_url = Go_Live_Update_URLS_Pro_Core::plugin_url( 'endpoint/domain-test-endpoint.php' );
		}

		$request_url = str_replace( $this->old_url, $this->new_url, $post_url );

		/**
		 * Multisite with subdirectories is impossible to test due to
		 * 1. WP redirects multisite requests when incoming so we can't load this plugin
		 * 2. We are unable to access the our custom endpoint from a sub directory url
		 *
		 * The $post_url will never include a /sub-directory in it therefore the $request_url
		 * will always be the same as the $post_url if the old url has a /sub-directory in it.
		 *
		 * @todo make additional warning message for different circumstances
		 *
		 */
		if ( $request_url === $post_url ) {
			$this->result = 'unknown';
			return;
		}

		$request = wp_remote_post( $request_url, $_args );
		$result  = json_decode( wp_remote_retrieve_body( $request ) );
		if ( empty( $result->success ) ) {
			$this->result = false;
		}
	}


	protected function get_message() {
		if ( true === $this->result ) {
			return $this->messages['pass'];
		}
		if ( false === $this->result ) {
			return $this->messages['fail'];
		}

		return $this->messages['warning'];
	}


	public function get_fixed() {
		return null; //this cannot be fixed
	}


	public function jsonSerialize() {
		$result = array(
			'test'          => __CLASS__,
			'result'        => $this->result,
			'label'         => esc_html__( 'Verify this site is accessible from the New URL.', 'go-live-update-urls' ),
			'message'       => $this->get_message(),
			'fix_available' => false,
		);

		return $result;
	}


	public static function factory( $old_url, $new_url ) {
		$class          = new self();
		$class->old_url = $old_url;
		$class->new_url = $new_url;
		$class->test();

		return $class;
	}

}
