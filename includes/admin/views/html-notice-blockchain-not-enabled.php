<?php
/**
 * Admin View: Notice - Blockchain not enabled.
 *
 * @package WooCommerce_ApusPayments/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'ApusPayments Disabled', 'woocommerce-apuspayments' ); ?></strong>: <?php printf( __( 'Please enable at least one blockchain to process payments.', 'woocommerce-apuspayments' )); ?>
	</p>
</div>
