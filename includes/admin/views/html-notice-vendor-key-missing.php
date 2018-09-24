<?php
/**
 * Admin View: Notice - VendorKey missing
 *
 * @package WooCommerce_ApusPayments/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'ApusPayments Disabled', 'woocommerce-apuspayments' ); ?></strong>: <?php _e( 'You should inform your vendorKey.', 'woocommerce-apuspayments' ); ?>
	</p>
</div>
