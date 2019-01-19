<?php
/**
 * class-groups-ws-subscriptions-table-renderer.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package groups-woocommerce
 * @since groups-woocommerce 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscription table renderer.
 */
class Groups_WS_Subscriptions_Table_Renderer {

	/**
	 * Renders a table with subscription information.
	 * @param array $options
	 * @param int $n will be set to the number of subscriptions found
	 */
	public static function render( $options, &$n ) {

		global $wpdb;

		$output = '';

		if ( isset( $options['user_id'] ) ) {
			$user = new WP_User( $options['user_id'] );
		} else {
			return $output;
		}

		$statuses = array( 'active' );
		$show_all = false;
		if ( isset( $options['status'] ) ) {
			$status = $options['status'];
			if ( is_string( $status ) ) {
				if ( trim( $status ) === '*' ) {
					$statuses = array( 'active', 'on-hold', 'cancelled', 'trash', 'deleted', 'switched', 'pending-cancel' );
				} else {
					$statuses = array();
					$_statuses = explode( ',', $status );
					foreach( $_statuses as $status ) {
						$status = strtolower( trim( $status ) );
						switch( $status ) {
							case 'active' :
							case 'on-hold' :
							case 'cancelled' :
							case 'trash' :
							case 'deleted' :
							case 'switched' :
							case 'pending-cancel' :
								$statuses[] = $status;
								break;
						}
					}
				}
			}
		}

		$exclude_cancelled_after_end_of_prepaid_term =
			isset( $options['exclude_cancelled_after_end_of_prepaid_term'] ) &&
			(
				( $options['exclude_cancelled_after_end_of_prepaid_term'] === true ) ||
				( $options['exclude_cancelled_after_end_of_prepaid_term'] == 'true' ) ||
				( $options['exclude_cancelled_after_end_of_prepaid_term'] == 'yes' )
			);

		$include_cancelled_orders =
		isset( $options['include_cancelled_orders'] ) &&
			(
				( $options['include_cancelled_orders'] === true ) ||
				( $options['include_cancelled_orders'] == 'true' ) ||
				( $options['include_cancelled_orders'] == 'yes' )
			);

		$include_refunded_orders =
		isset( $options['include_refunded_orders'] ) &&
		(
				( $options['include_refunded_orders'] === true ) ||
				( $options['include_refunded_orders'] == 'true' ) ||
				( $options['include_refunded_orders'] == 'yes' )
		);

		if ( function_exists( 'wcs_get_users_subscriptions' ) ) {
			$results = array();
			foreach ( wcs_get_users_subscriptions( $user->ID ) as $subscription ) {
				$results[ wcs_get_old_subscription_key( $subscription ) ] = wcs_get_subscription_in_deprecated_structure( $subscription );
			}
		} else {
			$results = WC_Subscriptions_Manager::get_users_subscriptions( $user->ID );
		}

		// pre-filter by status
		$_results = array();
		foreach( $results as $result_key => $result ) {
			$valid = false;
			if ( in_array( $result['status'], $statuses ) ) {
				$valid = true;
			}
			// exclude subscriptions from cancelled or refunded orders
			if ( isset( $result['order_id'] ) ) {
				if ( $order = Groups_WS_Helper::get_order( $result['order_id'] ) ) {
					switch( $order->get_status() ) {
						case 'cancelled' :
							if ( !$include_cancelled_orders ) {
								$valid = false;
							}
							break;
						case 'refunded' :
							if ( !$include_refunded_orders ) {
								$valid = false;
							}
							break;
					}
				}
			}
			if ( $exclude_cancelled_after_end_of_prepaid_term && ( $result['status'] == 'cancelled' ) ) {
				$hook_args = array( 'user_id' => ( int ) $user->ID, 'subscription_key' => $result_key );
				$end_timestamp = wp_next_scheduled( 'scheduled_subscription_end_of_prepaid_term', $hook_args );
				if ( ( $end_timestamp === false ) || ( $end_timestamp <= time() ) ) {
					$valid = false;
				}
			}
			if ( $valid ) {
				$_results[$result_key] = $result;
			}
		}
		$results = $_results;

		$n = count( $results );

		if ( $n > 0 ) {

			$column_display_names = array(
				'status'            => __( 'Status', 'groups-woocommerce' ),
				'title'             => __( 'Subscription', 'groups-woocommerce' ),
				'start_date'        => __( 'Start Date', 'groups-woocommerce' ),
				'expiry_date'       => __( 'Expiration', 'groups-woocommerce' ),
				'end_date'          => __( 'End Date', 'groups-woocommerce' ),
				'trial_expiry_date' => __( 'Trial Expiration', 'groups-woocommerce' ),
				'groups'            => __( 'Groups', 'groups-woocommerce' ),
				'order_id'          => __( 'Order', 'groups-woocommerce' ),
			);

			if ( isset( $options['columns'] ) && $options['columns'] !== null ) {
				if ( is_string( $options['columns'] ) ) {
					$columns = explode( ',', $options['columns'] );
					$_columns = array();
					foreach( $columns as $column ) {
						$_columns[] = trim( $column );
					}
					$options['columns'] = $_columns;
				}
				$new_columns = array();
				foreach( $options['columns'] as $key ) {
					if ( key_exists( $key, $column_display_names ) ) {
						$new_columns[$key] = $column_display_names[$key];
					}
				}
				$column_display_names = $new_columns;
			}

			if ( count( $column_display_names )  > 0 ) {
				$output .= '<table class="subscriptions">';
				$output .= '<thead>';
				$output .= '<tr>';

				foreach ( $column_display_names as $key => $column_display_name ) {
					$output .= "<th scope='col' class='$key'>$column_display_name</th>";
				}

				$output .= '</tr>';
				$output .= '</thead>';
				$output .= '<tbody>';

				$i = 0;
				foreach( $results as $result_key => $result ) {

					$order = Groups_WS_Helper::get_order( $result['order_id'] );

					// this method throws up a fatal error, it's buggy because it doesn't understand the item ...
					//$order_item = WC_Subscriptions_Order::get_item_by_product_id( $order, $result['product_id'] );
					//$product    = $order_item->get_product();
					// ... so we do our own:
					$product = null;
					if ( $order ) {
						foreach ( $order->get_items() as $item ) {
							$item_order_id = $item->get_order_id();
							$item_product_id = $item->get_product_id();
							if ( ( $item_order_id == $result['order_id'] ) && ( $item_product_id == $result['product_id'] ) ) {
								$product = $item->get_product();
								break;
							}
						}
					} else if ( function_exists( 'wc_get_product' ) ) {
						if ( empty( $result['variation_id'] ) ) {
							$product = wc_get_product( $result['product_id'] );
						} else {
							$product = wc_get_product( $result['variation_id'] );
						}
					}

					$output .= '<tr class="' . ( $i % 2 == 0 ? 'even' : 'odd' ) . '">';

					foreach( $column_display_names as $column_key => $column_title ) {
						$output .= sprintf( '<td class="%s">', $column_key );
						switch( $column_key ) {
							case 'status' :
								if ( function_exists( 'wcs_get_subscription_status_name' ) ) {
									$output .= wcs_get_subscription_status_name( $result['status'] );
								} else {
									$output .=  WC_Subscriptions_Manager::get_status_to_display( $result['status'], $result_key, $user->ID );
								}
								break;
							case 'title' :
								if ( $product !== null && method_exists( $product, 'get_name' ) ) {
									$output .= $product->get_name();
								} else {
									// @ WCS 2.2.5 this method is buggy and produces PHP Notice:  Object of class WC_Order_Item_Product could not be converted to int
									$output .= WC_Subscriptions_Order::get_item_name( $result['order_id'], $result['product_id'] );
								}
								if ( $product !== null ) {
									$variation_data = wc_get_product_variation_attributes( $product->get_id() );
									if ( !empty( $variation_data ) ) {
										$column_content .= '<br />';
										if ( function_exists( 'wc_get_formatted_variation' ) ) {
											$column_content .= wc_get_formatted_variation( $variation_data, true );
										} else {
											$column_content .= woocommerce_get_formatted_variation( $variation_data, true );
										}
									}
								}
								break;
							case 'start_date' :
							case 'expiry_date' :
							case 'end_date' :
								if ( $column_key == 'expiry_date' && $result[$column_key] == 0 ) {
									$output .= __( 'Never', 'groups-woocommerce' );
								} else if ( $column_key == 'end_date' && $result[$column_key] == 0 ) {
									$output .= __( 'Not yet ended', 'groups-woocommerce' );
								} else {
									$user_timestamp = strtotime( $result[$column_key] ) + ( get_option( 'gmt_offset' ) * 3600 );
									$output .= sprintf( '<time title="%s">%s</time>', esc_attr( $user_timestamp ), date_i18n( get_option( 'date_format' ), $user_timestamp ) );
								}
								break;
							case 'trial_expiry_date' :
								if ( isset( $result['trial_expiry_date'] ) ) {
									$trial_expiration = $result['trial_expiry_date'];
								} else {
									$trial_expiration = WC_Subscriptions_Manager::get_trial_expiration_date( $result_key, $user->ID, 'timestamp' );
								}
								if ( empty( $trial_expiration ) ) {
									$output .= '-';
								} else {
									$trial_expiration = $trial_expiration + ( get_option( 'gmt_offset' ) * 3600 );
									$output .= sprintf( '<time title="%s">%s</time>', esc_attr( $trial_expiration ), date_i18n( get_option( 'date_format' ), $trial_expiration ) );
								}
								break;
							case 'groups' :
								if ( $product_groups = get_post_meta( $result['product_id'], '_groups_groups', false ) ) {
									if ( count( $product_groups )  > 0 ) {
										$output .= '<ul>';
										foreach( $product_groups as $group_id ) {
											if ( $group = Groups_Group::read( $group_id ) ) {
												$output .= '<li>' . wp_filter_nohtml_kses( $group->name ) . '</li>';
											}
										}
										$output .= '</ul>';
									}
								}
								break;
							case 'order_id' :
								if ( !empty( $result['order_id'] ) ) {
									$output .= sprintf( __( 'Order %d', 'groups-woocommerce' ), $result['order_id'] );
								}
								break;
						}
						$output .= '</td>';
					}
					$output .= '</tr>';
					$i++;

				}
				$output .= '</tbody>';
				$output .= '</table>';
			}
		}

		return $output;
	}
}
