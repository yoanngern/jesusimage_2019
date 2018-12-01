<?php
/**
 * WP Multisite redirects incoming ajax requests to a matching
 * site, which make our domain test always fail on multisite.
 *
 * This replaces the ajax request to allow running the test on
 * multisite.
 *
 * @since 2.0.2
 *
 *
 */

@header( 'Content-Type: application/json;' );

if( empty( $_POST[ 'hash' ] ) ){
	echo json_encode( array( 'success' => false ) );
} elseif( md5( __FILE__ ) === $_POST[ 'hash' ] ) {
	echo json_encode( array( 'success' => true ) );
} else {
	echo json_encode( array( 'success' => false, __FILE__ ) );
}

die();