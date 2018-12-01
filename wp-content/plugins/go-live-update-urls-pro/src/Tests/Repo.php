<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Repo
 *
 * @author Mat Lipe
 * @since  2.0.0
 *
 */
class Go_Live_Update_URLS_Pro_Tests_Repo {
	/**
	 * tests
	 *
	 * @var array
	 */
	protected $tests;


	protected function __construct() {
		$this->tests = apply_filters( 'go-live-update-urls-pro/url-tests', array(
			'Go_Live_Update_URLS_Pro_Tests_Trailing_Slash' => true,
			'Go_Live_Update_URLS_Pro_Tests_Scheme'         => true,
			'Go_Live_Update_URLS_Pro_Tests_Domain'         => true,
		) );
	}


	public function get_all_results( $old_url, $new_url ) {
		$results = array();
		foreach( (array) $this->tests as $_test => $_enabled ){
			if( $this->test_enabled( $_test ) ){
				/** @var \Go_Live_Update_URLS_Pro_Tests_Abstract $test */
				$test = call_user_func( "$_test::factory", $old_url, $new_url );
				$results[] = $test;
			}
		}

		return $results;
	}


	public function register_test( $class_name, $enabled = true ) {
		$this->tests[ $class_name ] = $enabled;
	}


	public function test_enabled( $class_name ) {
		return isset( $this->tests[ $class_name ] ) && $this->tests[ $class_name ];
	}

	//********** SINGLETON **********/


	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;


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
