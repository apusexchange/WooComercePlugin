<?php
/**
 * Plain email instructions.
 *
 * @author  ApusPayments
 * @package WooCommerce_ApusPayments/Templates
 * @version 2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

_e( 'Payment', 'woocommerce-apuspayments' );

echo "\n\n";

echo sprintf( __( 'You just made the payment in %s using the %s.', 'woocommerce-apuspayments' ), $installments . 'x', $method );

echo "\n";

_e( 'As soon as the operator confirm the payment, your order will be processed.', 'woocommerce-apuspayments' );

echo "\n\n****************************************************\n\n";
