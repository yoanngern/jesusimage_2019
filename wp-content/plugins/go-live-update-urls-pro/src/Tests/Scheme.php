<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Scheme
 *
 * @author Mat Lipe
 * @since  2.0.0
 *
 */
class Go_Live_Update_URLS_Pro_Tests_Scheme extends Go_Live_Update_URLS_Pro_Tests_Abstract {
	public $messages = array();


	protected function __construct() {
		$this->messages = apply_filters( 'go-live-update-urls-pro/tests/scheme-messages', array(
			'both-scheme' => esc_html__( 'Both urls have a scheme.', 'go-live-update-urls' ),
			'no-scheme'   => esc_html__( 'Both urls do not have a scheme.', 'go-live-update-urls' ),
			'old-scheme'  => esc_html__( 'The old url has a scheme. The new url does not.', 'go-live-update-urls' ),
			'new-scheme'  => esc_html__( 'The new url has a scheme. The old url does not.', 'go-live-update-urls' ),
		) );
	}


	protected function test() {
		$old_scheme = parse_url( $this->old_url );
		$new_scheme = parse_url( $this->new_url );
		$this->result = true;

		if( !empty( $old_scheme[ 'scheme' ] ) && empty( $new_scheme[ 'scheme' ] ) ){
			$this->result = false;
		} elseif( !empty( $new_scheme[ 'scheme' ] ) && empty( $old_scheme[ 'scheme' ] ) ) {
			$this->result = false;
		}
	}


	protected function get_message() {
		$old_scheme = parse_url( $this->old_url );

		if( $this->result ){
			if( !empty( $old_scheme[ 'scheme' ] ) ){
				return $this->messages[ 'both-scheme' ];
			}

			return $this->messages[ 'no-scheme' ];
		}

		if( empty( $old_scheme[ 'scheme' ] ) ){
			return $this->messages[ 'new-scheme' ];
		}

		return $this->messages[ 'old-scheme' ];

	}


	public function get_fixed() {
		if( $this->result ){
			return $this->new_url;
		}
		$old_scheme = parse_url( $this->old_url );
		$new_scheme = parse_url( $this->new_url );

		if( empty( $old_scheme[ 'scheme' ] ) ){
			if( 0 === strpos( $this->old_url, '//' ) ){
				return str_replace( $new_scheme[ 'scheme' ] . ':', '', $this->new_url );
			}

			return str_replace( $new_scheme[ 'scheme' ] . '://', '', $this->new_url );
		}

		$new_url = str_replace( '//', '', $this->new_url );

		return $old_scheme[ 'scheme' ] . '://' . $new_url;
	}


	public function jsonSerialize() {
		$result = array(
			'test'          => __CLASS__,
			'result'        => $this->result,
			'label'         => esc_html__( 'Verify the Old URL and New URL have matching structures.', 'go-live-update-urls' ),
			'message'       => $this->get_message(),
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