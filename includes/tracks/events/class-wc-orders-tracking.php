<?php
/**
 * WooCommerce Orders Tracking
 *
 * @package WooCommerce\Tracks
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class adds actions to track usage of WooCommerce Orders.
 */
class WC_Orders_Tracking {
	/**
	 * Init tracking.
	 */
	public function init() {
		add_action( 'post_updated', array( $this, 'track_order_actions' ), 10, 2 );
		add_action( 'woocommerce_new_order_item', array( $this, 'track_edit_order_add_item' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'track_order_status_change' ), 10, 3 );
		add_action( 'load-edit.php', array( $this, 'track_orders_view' ), 10 );
	}

	/**
	 * Send a Tracks event when an order line item is added.
	 */
	function track_edit_order_add_item( $item_id, $item ) {

		$type = null;

		if ( $item instanceof WC_Order_Item_Product ) {
			$type = 'product';
		} else if ( $item instanceof WC_Order_Item_Fee ) {
			$type = 'fee';
		} else if ( $item instanceof WC_Order_Item_Shipping ) {
			$type = 'shipping';
		} else if ( $item instanceof WC_Order_Item_Tax ) {
			$type = 'tax';
		} else if ( $item instanceof WC_Order_Item_Coupon ) {
			$type = 'coupon';
		}

		if ( $type ) {
			WC_Tracks::record_event( 'orders_edit_order_add_item', array(
				'type' => $type,
			) );
		}
	}

	/**
	 * Send a Tracks event when an order action is taken.
	 */
	public function track_order_actions() {
		if ( ! empty( $_POST['wc_order_action'] ) ) {

			$action = wc_clean( wp_unslash( $_POST['wc_order_action'] ) );

			WC_Tracks::record_event( 'orders_edit_order_action', array(
				'action' => $action,
			) );
		}
	}

	/**
	 * Send a Tracks event when the Orders page is viewed.
	 */
	public function track_orders_view() {
		if ( isset( $_GET['post_type'] ) && 'shop_order' === wp_unslash( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			WC_Tracks::record_event( 'orders_view' );
		}
	}

	/**
	 * Send a Tracks event when an order status is changed.
	 *
	 * @param int    $id Order id.
	 * @param string $previous_status the old WooCommerce order status.
	 * @param string $next_status the new WooCommerce order status.
	 */
	public function track_order_status_change( $id, $previous_status, $next_status ) {
		$order = wc_get_order( $id );
		$date  = $order->get_date_created();

		$properties = array(
			'order_id'        => $id,
			'next_status'     => $next_status,
			'previous_status' => $previous_status,
			'date_created'    => $date->date( 'Y-m-d' ),
		);

		WC_Tracks::record_event( 'orders_edit_status_change', $properties );
	}
}
