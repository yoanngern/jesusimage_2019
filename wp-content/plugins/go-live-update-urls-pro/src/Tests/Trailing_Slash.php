<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Trailing_Slash
 *
 * @author Mat Lipe
 * @since  2.0.0
 *
 */
class Go_Live_Update_URLS_Pro_Tests_Trailing_Slash extends Go_Live_Update_URLS_Pro_Tests_Abstract {

	public $messages = array();


	protected function __construct() {
		$this->messages = apply_filters( 'go-live-update-urls-pro/tests/trailing-slash-messages', array(
			'both-trail' => esc_html__( 'Both urls have a trailing slash.', 'go-live-update-urls' ),
			'no-trail'   => esc_html__( 'Both urls do not have a trailing slash.', 'go-live-update-urls' ),
			'old-trail'  => esc_html__( 'The old url has a trailing slash. The new url does not.', 'go-live-update-urls' ),
			'new-trail'  => esc_html__( 'The new url has a trailing slash. The old url does not.', 'go-live-update-urls' ),
		) );
	}


	protected function test() {
		$old_slash = substr( $this->old_url, - 1 );
		$new_slash = substr( $this->new_url, - 1 );
		$this->result = true;

		if( $old_slash === '/' && $new_slash !== '/' ){
			$this->result = false;
		} elseif( $new_slash === '/' && $old_slash !== '/' ) {
			$this->result = false;
		}
	}


	protected function get_message() {
		if( $this->result ){
			if( substr( $this->old_url, - 1 ) === '/' ){
				return $this->messages[ 'both-trail' ];
			}

			return $this->messages[ 'no-trail' ];
		}

		if( substr( $this->old_url, - 1 ) === '/' ){
			return $this->messages[ 'old-trail' ];
		}

		return $this->messages[ 'new-trail' ];

	}


	public function get_fixed() {
		if( $this->result ){
			return $this->new_url;
		}

		if( substr( $this->new_url, - 1 ) === '/' ){
			return untrailingslashit( $this->new_url );
		}

		return trailingslashit( $this->new_url );
	}


	/**
	 * Get the status of the url combinations and messages
	 * which go along with them
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$result = array(
			'test'    => __CLASS__,
			'result'  => $this->result,
			'label'   => esc_html__( 'Verify the Old URL and New URL have matching trailing slashes.', 'go-live-update-urls' ),
			'message' => $this->get_message(),
			'fix_available' => true,
		);

		return $result;
	}

	public static function factory( $old_url, $new_url){
		$class = new self();
		$class->old_url = $old_url;
		$class->new_url = $new_url;
		$class->test();
		return $class;
	}

}