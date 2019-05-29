<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NF_FU_Helper
 *
 * The Static Helper Class
 *
 * Provides helper functionality for File Uploads
 */
final class NF_FU_Helper {

	public static function random_string( $length = 10 ) {
		$characters    = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random_string;
	}

	/**
	 * Get the lowest integer in MB for upload max size.
	 *
	 * @return float|int|mixed
	 */
	public static function max_upload_mb_int() {
		$u_bytes = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$p_bytes = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

		$max = min( $u_bytes, $p_bytes );

		$max = $max / MB_IN_BYTES;

		return $max;
	}

	/**
	 * Are we on the FU page?
	 *
	 * @param string $tab
	 * @param array  $args
	 *
	 * @return bool
	 */
	public static function is_page( $tab = '', $args = array() ) {
		global $pagenow;

		if ( 'admin.php' !== $pagenow ) {
			return false;
		}

		$defaults = array( 'page' => 'ninja-forms-uploads' );

		if ( $tab ) {
			$defaults['tab'] = $tab;
		}

		$args = array_merge( $args, $defaults );

		foreach ( $args as $key => $value ) {
			if ( ! isset( $_GET[ $key ] ) ) {
				return false;
			}

			if ( false !== $value && $value !== $_GET[ $key ] ) {
				return false;
			}
		}

		return true;
	}

} // End Class WPN_Helper
