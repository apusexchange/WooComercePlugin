<?php
/**
 * HTML email instructions.
 *
 * @author  ApusPayments
 * @package WooCommerce_ApusPayments/Templates
 * @version 2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<h2><?php _e( 'Payment', 'woocommerce-apuspayments' ); ?></h2>

<p class="order_details"><?php echo sprintf( __( 'You just made the payment in %s using the %s.', 'woocommerce-apuspayments' ), '<strong>' . $installments . 'x</strong>', '<strong>' . $method . '</strong>' ); ?><br /><?php _e( 'As soon as the operator confirm the payment, your order will be processed.', 'woocommerce-apuspayments' ); ?></p>
