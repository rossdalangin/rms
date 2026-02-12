<?php
namespace ResortManager\Gateways;

class WooCommerce {
	public function __construct() {
		add_action( 'wp_ajax_resort_woocommerce_checkout', [ $this, 'process_payment' ] );
		add_action( 'wp_ajax_nopriv_resort_woocommerce_checkout', [ $this, 'process_payment' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'handle_order_completed' ], 10, 1 );
		add_action( 'woocommerce_order_status_processing', [ $this, 'handle_order_completed' ], 10, 1 );
	}

	public function process_payment() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		if ( ! class_exists( 'WooCommerce' ) || get_option( 'resort_payment_woocommerce_enabled' ) !== '1' ) {
			wp_send_json_error( [ 'message' => __( 'WooCommerce integration is disabled.', 'resort-manager' ) ] );
		}

		$booking_id = intval( $_POST['booking_id'] );
		$total_price = get_post_meta( $booking_id, '_resort_total_price', true );
		$deposit_percent = get_option( 'resort_deposit_percentage', '100' );
		$payable_amount = ( floatval($total_price) * intval($deposit_percent) ) / 100;

		// 1. Create WooCommerce Order
		$order = wc_create_order();
		$order->add_product( $this->get_virtual_booking_product(), 1, [
			'subtotal' => $payable_amount,
			'total'    => $payable_amount,
		] );

		// Set order details
		$order->set_customer_id( get_post_meta( $booking_id, '_resort_guest_id', true ) );
		$order->update_meta_data( '_resort_booking_id', $booking_id );
		$order->set_total( $payable_amount );
		$order->save();

		// 2. Clear WC Cart and add this order (optional, usually redirect to pay page)
		// Return pay url
		wp_send_json_success( [
			'checkout_url' => $order->get_checkout_payment_url()
		] );
	}

	private function get_virtual_booking_product() {
		// In a real scenario, we might want a dedicated 'Booking' product or just use a placeholder
		// For simplicity, we'll return a fake product ID or handle it via meta
		return null; // WC allows adding products by ID, but we can also add items directly
	}

	public function handle_order_completed( $order_id ) {
		$order = wc_get_order( $order_id );
		$booking_id = $order->get_meta( '_resort_booking_id' );

		if ( $booking_id ) {
			\ResortManager\Core\BookingManager::confirm_booking( $booking_id, 'WC-' . $order_id, 'woocommerce' );
		}
	}
}
