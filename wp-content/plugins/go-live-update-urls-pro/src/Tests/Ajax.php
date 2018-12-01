<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Ajax
 *
 * @author Mat Lipe
 * @since  10/30/2017
 *
 */
class Go_Live_Update_URLS_Pro_Tests_Ajax {
	const ALL_RESULTS = 'go-live-update-urls-pro/tests/all-results';
	const GET_FIXED = 'go-live-update-urls-pro/tests/get-fixed';

	const VERIFY_DOMAIN = 'go-live-update-urls-pro/tests/verify-domain';
	const DOMAIN_KEY = 'go-live-update-urls-pro/test/domain-key';


	private function hook() {
		add_action( 'wp_ajax_' . self::GET_FIXED, array( $this, 'ajax_get_fixed' ) );
		add_action( 'wp_ajax_' . self::ALL_RESULTS, array( $this, 'ajax_all_results' ) );
		add_action( 'admin_post_nopriv_' . self::VERIFY_DOMAIN, array( $this, 'post_verify_domain' ) );
	}


	public function post_verify_domain() {
		if( empty( $_POST[ self::DOMAIN_KEY ] ) ){
			wp_send_json_error();
		}
		if( $_POST[ self::DOMAIN_KEY ] === site_url() ){
			wp_send_json_success();
		}
		wp_send_json_error();
	}


	/**
	 * Get the results of all tests against a supplied old and new url
	 *
	 * @return void
	 */
	public function ajax_all_results() {
		if( !isset( $_POST[ 'old_url' ], $_POST[ 'new_url' ] ) ){
			wp_send_json_error( esc_html__( 'Incorrect test data supplied.', 'go-live-update-urls' ) );
		}
		$old_url = sanitize_text_field( $_POST[ 'old_url' ] );
		$new_url = sanitize_text_field( $_POST[ 'new_url' ] );
		$results = Go_Live_Update_URLS_Pro_Tests_Repo::instance()->get_all_results( $old_url, $new_url );

		wp_send_json_success( $results );

	}


	/**
	 * Get the fixed version of a url which failed a test
	 * Supply the test and this will return the fixed as well
	 * as the full test results.
	 *
	 * @notice This should never update any data anywhere
	 *
	 * @return void
	 */
	public function ajax_get_fixed() {
		$repo = Go_Live_Update_URLS_Pro_Tests_Repo::instance();
		if( !isset( $_POST[ 'test' ] ) || !$repo->test_enabled( $_POST[ 'test' ] ) ){
			wp_send_json_error( esc_html__( 'Incorrect test class supplied.', 'go-live-update-urls' ) );
		} elseif( !isset( $_POST[ 'old_url' ], $_POST[ 'new_url' ] ) ) {
			wp_send_json_error( esc_html__( 'Incorrect test data supplied.', 'go-live-update-urls' ) );
		}

		/** @var \Go_Live_Update_URLS_Pro_Tests_Abstract $test */
		$test = call_user_func( $_POST[ 'test' ] . '::factory', sanitize_text_field( $_POST[ 'old_url' ] ), sanitize_text_field( $_POST[ 'new_url' ] ) );
		$fixed = $test->get_fixed();
		$test = call_user_func( $_POST[ 'test' ] . '::factory', sanitize_text_field( $_POST[ 'old_url' ] ), $fixed );

		$data = array(
			'fixed'   => $fixed,
			'results' => $test->jsonSerialize(),
		);

		wp_send_json_success( $data );
	}


	//********** SINGLETON **********/


	/**
	 * Instance of this class for use as singleton
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
		if( !is_a( self::$instance, __CLASS__ ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}
}