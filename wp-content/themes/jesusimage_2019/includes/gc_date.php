<?php


/**
 * @param $query
 * @param string $post_type
 * @param string $post_cat
 *
 * @return mixed
 */
function order_dates( $query, $post_type = 'gc_event', $post_cat = 'gc_eventcategory', $post_status = 'publish' ) {

	if ( $post_status == 'all' ) {
		$post_status = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' );
	}

	if ( is_admin() && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == $post_type ) {
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'start' );
		$query->set( 'meta_key', 'end' );
		$query->set( 'order', 'desc' );
		$query->set( 'post_status', $post_status );

		return $query;
	}

	if ( ! $query->is_main_query() ) {

		return $query;
	}

	// only modify queries for category
	if ( isset( $query->query_vars[ $post_cat ] ) ) {
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'start' );
		$query->set( 'meta_key', 'end' );
		$query->set( 'order', 'asc' );

		$today = date( 'Y-m-d H:i:s' );

		$query->set( 'meta_query', array(
			array(
				'key'     => 'end',
				'compare' => '>=',
				'value'   => $today,
			)
		) );

		return $query;
	}

	// only modify queries for 'gc_event' post type
	if ( ! is_single() && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == $post_type ) {

		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'start' );
		$query->set( 'meta_key', 'end' );
		$query->set( 'order', 'asc' );

		$today = date( 'Y-m-d H:i:s' );

		$query->set( 'meta_query', array(
			'relation' => 'AND',
			array(
				'key'     => 'end',
				'compare' => '>=',
				'value'   => $today,
			),
			array(
				'key'     => 'events_show',
				'compare' => '=',
				'value'   => true,
			)
		) );

		return $query;


	}


}


/**
 * @param $post_id
 */
function update_dates( $post_id ) {

	$start_date = get_field( 'start_date' );
	$start_time = get_field( 'start_time' );

	$end_date = get_field( 'end_date' );
	$end_time = get_field( 'end_time' );

	if ( $end_date == null ) {
		$end_date = $start_date;
	}

	if ( $end_time == null ) {
		$end_time = $start_time;
	}

	$start = new DateTime( $start_date . " " . $start_time );
	$end   = new DateTime( $end_date . " " . $end_time );

	update_field( 'start', date_format( $start, 'Y-m-d H:i:s' ), $post_id );
	update_field( 'end', date_format( $end, 'Y-m-d H:i:s' ), $post_id );

	update_field( 'start_date', $start_date, $post_id );
	update_field( 'start_time', $start_time, $post_id );
	update_field( 'end_date', $end_date, $post_id );
	update_field( 'end_time', $end_time, $post_id );

}


/**
 * @param $start
 * @param $end
 *
 * @return string
 */
function complex_date( $start, $end ) {


	$start = new DateTime( $start );
	$end   = new DateTime( $end );

	$start_t = $start->getTimestamp();
	$end_t   = $end->getTimestamp();


	if ( date_format( $start, 'Y-m-d' ) != date_format( $end, 'Y-m-d' ) ):

		// French
		if ( get_locale() == "fr_FR" ) :

			if ( date( 'Y', $start_t ) == date( 'Y', $end_t ) ):

				if ( date( 'm', $start_t ) == date( 'm', $end_t ) ):

					$date = date_i18n( 'j', $start_t ) . ' - ' . date_i18n( 'j F Y', $end_t );

				else:

					$date = date_i18n( 'j M', $start_t ) . ' - ' . date_i18n( 'j M Y', $end_t );

				endif;

			else:

				$date = date_i18n( 'j M Y', $start_t ) . ' - ' . date_i18n( 'j M Y', $end_t );

			endif;


		// English
		else:

			if ( date( 'Y', $start_t ) == date( 'Y', $end_t ) ):

				if ( date( 'm', $start_t ) == date( 'm', $end_t ) ):

					$date = date_i18n( 'M jS', $start_t ) . ' - ' . date_i18n( 'jS Y', $end_t );

				else:

					$date = date_i18n( 'M jS', $start_t ) . ' - ' . date_i18n( 'M jS Y', $end_t );

				endif;

			else:

				$date = date_i18n( 'M j, Y', $start_t ) . ' - ' . date_i18n( 'M j, Y', $end_t );

			endif;

		endif;

	// one day
	else:

		$date = date_i18n( get_option( 'date_format' ), $start_t );

	endif;

	return $date;

}


/**
 * @param $start
 * @param $end
 */
function complex_time( $start, $end ) {


	$time = "";

	$start = new DateTime( $start );
	$end   = new DateTime( $end );


	$start_t = $start->getTimestamp();
	$end_t   = $end->getTimestamp();


	if ( get_locale() == "fr_FR" ) {
		$time_format = 'G\hi';
	} else {
		$time_format = 'g:i a';
	}


	if ( ( date( 'Gi', $start_t ) == '000' ) && ( date( 'Gi', $end_t ) == '000' ) ):

		$time = "";

	elseif ( date( 'Gi', $start_t ) == date( 'Gi', $end_t ) ) :

		$time = date( $time_format, $start_t );

	else:

		$time = date( $time_format, $start_t ) . " - " . date( $time_format, $end_t );

	endif;


	return $time;

}


/**
 * @param $date
 *
 * @return string
 */
function time_trans( $date ) {
	if ( get_locale() == "fr_FR" ) :

		if ( $date->format( 'i' ) == '00' ) {

			$time = date_i18n( 'G\h', strtotime( $date->format( 'H:i' ) ) );

		} else {

			$time = date_i18n( 'G\hi', strtotime( $date->format( 'H:i' ) ) );

		}

	else:
		$time = date_i18n( 'g:i a', strtotime( $date->format( 'H:i' ) ) );
	endif;

	return $time;
}


/**
 * @param $start
 * @param $end
 * @param array $event_cat
 * @param array $service_cat
 * @param bool $weekend
 *
 * @return array
 */
function get_dates( $start, $end, $event_cat = array(), $service_cat = array(), $weekend = false ) {

	$_event_cat   = $event_cat;
	$_service_cat = $service_cat;

	if ( $_event_cat === false ) {

		$terms = get_terms( array(
			'taxonomy' => 'gc_eventcategory',
		) );

		foreach ( $terms as $term ) {

			$_event_cat[] = $term->slug;
		}

	}


	$special_query = '';

	if ( $_service_cat === false ) {

		$terms = get_terms( array(
			'taxonomy' => 'gc_servicecategory',
		) );

		foreach ( $terms as $term ) {

			$_service_cat[] = $term->slug;
		}
	} else {
		$special_query = array(
			'relation' => 'OR',
			array(
				'key'     => 'service_type',
				'compare' => 'EXISTS',
			)
		);

		foreach ( $service_cat as $cat ) {
			$special_query[] = array(
				'key'     => 'event_service_type',
				'compare' => '=',
				'value'   => get_term_by( 'slug', $cat, 'gc_servicecategory' )->term_id,
			);
		}
	}

	if ( $weekend ) {


		$weekend_query = array(
			'relation' => 'OR',
			array(
				'key'     => 'service_type',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => 'weekend_show',
				'compare' => '=',
				'value'   => true,
			)
		);
	} else {
		$weekend_query = '';
	}

	$args = array(
		'posts_per_page' => 50,
		'orderby'        => 'meta_value',
		'meta_key'       => 'start',
		'order'          => 'asc',
		'tax_query'      => array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'gc_eventcategory',
				'field'    => 'slug',
				'terms'    => $_event_cat,
			),
			array(
				'taxonomy' => 'gc_servicecategory',
				'field'    => 'slug',
				'terms'    => $_service_cat,
			),

		),
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'end',
				'compare' => '>=',
				'value'   => $start,
			),
			$special_query,
			array(
				'key'     => 'start',
				'compare' => '<=',
				'value'   => $end,
			),
			$weekend_query

		),

	);

	// The Query
	$query = new WP_Query( $args );

	$dates_return = $query->get_posts();

	/* Restore original Post Data */
	wp_reset_postdata();


	return $dates_return;

}