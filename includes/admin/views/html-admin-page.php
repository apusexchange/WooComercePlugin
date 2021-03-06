<?php
/**
 * Admin options screen.
 *
 * @package WooCommerce_ApusPayments/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo esc_html( $this->method_title ); ?></h3>

<?php
	if ( 'yes' == $this->get_option( 'enabled' ) ) {
		if ( ! $this->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
			include dirname( __FILE__ ) . '/html-notice-currency-not-supported.php';
		}

		if ( ! $this->has_enable_any_blockchain() && ! class_exists( 'woocommerce_wpml' ) ) {
			include dirname( __FILE__ ) . '/html-notice-blockchain-not-enabled.php';
		}

		if ( '' === $this->get_vendor_key() ) {
			include dirname( __FILE__ ) . '/html-notice-vendor-key-missing.php';
		}
	}
?>

<?php echo wpautop( $this->method_description ); ?>

<?php include dirname( __FILE__ ) . '/html-admin-help-message.php'; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
