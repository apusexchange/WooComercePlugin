<?php
/**
 * Checkout form.
 *
 * @author  ApusPayments
 * @package WooCommerce_ApusPayments/Templates
 * @version 2.12.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="apuspayments-payment-form" class="<?php echo 'storefront' === basename( get_template_directory() ) ? 'woocommerce-apuspayments-form-storefront' : ''; ?>" data-cart_total="<?php echo esc_attr( number_format( $cart_total, 2, '.', '' ) ); ?>">	

	<div id="apuspayments-card-number-form">
		<div class="form-row col1">
			<div class='card-wrapper'></div>
			<p class="form-helper">
				<?php echo __( 'Enter the card number (8-digits) registered in your ApusPayments account.', 'woocommerce-apuspayments' ); ?></br></br>
				<?php echo __( 'Not have an account yet? <a target="_blank" href="https://apuspayments.com/card">Get my account</a>.', 'woocommerce-apuspayments' ); ?>
			</p>
		</div>		
		<div class="form-row col2">
			<p id="apuspayments-card-number-field" class="form-row">
				<label for="apuspayments-card-number">
					<?php _e( 'Card Number', 'woocommerce-apuspayments' ); ?> 
					<span class="required">*</span>
				</label>
				<input id="apuspayments-card-number" class="input-text wc-card-number-form-card-number" type="text" maxlength="20" autocomplete="off" name="apuspayments-card-number" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="padding: 8px;" />
			</p>
			<div class="clear"></div>
			<p id="apuspayments-card-password-field" class="form-row">
				<label for="apuspayments-card-password">
					<?php _e( 'Password', 'woocommerce-apuspayments' ); ?> 
					<span class="required">*</span>
				</label>
				<input id="apuspayments-password" class="input-text wc-card-number-form-password" type="password" autocomplete="off" name="apuspayments-card-password" placeholder="<?php _e( 'Password', 'woocommerce-apuspayments' ); ?>" style="padding: 8px;" />
			</p>
			<div class="clear"></div>
			<p id="apuspayments-card-password-field" class="form-row" style="margin: 0">
				<label for="apuspayments-card-password">
					<?php _e( 'Blockchain', 'woocommerce-apuspayments' ); ?> 
					<span class="required">*</span>
				</label>
				<select id="apuspayments-card-blockchain" name="apuspayments-blockchain" class="apuspayments-card-blockchain" autocomplete="country">
					<option value=""><?php _e( 'Select a blockchain', 'woocommerce-apuspayments' ); ?> </option>
					<option value="BTC" data-blockchain-name="bitcoin">BTC - Bitcoin</option>
					<option value="ETH" data-blockchain-name="ethereum">ETH - Ethereum</option>
					<option value="LTC" data-blockchain-name="litecoin">LTC - Litecoin</option>
				</select>
				<span id="apuspayments-total-blockchain"></span>
			</p>

		</div>
	</div>
</fieldset>
