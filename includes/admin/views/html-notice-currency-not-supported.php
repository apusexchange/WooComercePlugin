<?php
/**
 * Admin View: Notice - Currency not supported.
 *
 * @package WooCommerce_ApusPayments/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'ApusPayments Disabled', 'woocommerce-apuspayments' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported.', 'woocommerce-apuspayments' ), get_woocommerce_currency() ); ?>
	</p>
</div>
