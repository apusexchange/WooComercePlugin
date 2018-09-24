<?php
/**
 * Admin help message.
 *
 * @package WooCommerce_ApusPayments/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( apply_filters( 'woocommerce_apuspayments_help_message', true ) ) : ?>
	<div class="updated inline woocommerce-message">
		<p><?php echo esc_html( sprintf( __( 'The ease of integration and security in transactions, knows a platform of payments of the future. #blockchain #bitcoin #litecoin #decred #ethereum', 'woocommerce-apuspayments' ), __( 'WooCommerce ApusPayments', 'woocommerce-apuspayments' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ) ); ?>			
		</p>
		<p>
			<a href="http://apuspayments.com" target="_blank" class="button button-primary">
				<?php esc_html_e( 'Know More', 'woocommerce-apuspayments' ); ?>
			</a> 
			<a href="http://docs.apuspayments.com" target="_blank" class="button button-secondary">
				<?php esc_html_e( 'Documentation', 'woocommerce-apuspayments' ); ?>
			</a>
		</p>
	</div>
<?php endif;
