/*global wc_apuspayments_params, ApusPaymentsDirectPayment, wc_checkout_params */
(function( $ ) {
	'use strict';

	$( function() {

		var apuspayments_submit = false;

		/**
		 * Format price.
		 *
		 * @param  {int|float} price
		 *
		 * @return {string}
		 */
		function apusPaymentsGetPriceText(coin, blockchain) {
			$.get( 'https://api.coinmarketcap.com/v1/ticker/' + coin + '/?convert=' + wc_apuspayments_params.order_currency, function( data ) {
			  let priceCoin = data[0]['price_' + wc_apuspayments_params.order_currency.toLowerCase()];
			  let priceCurrency = wc_apuspayments_params.order_total_price;
			  let priceParsed = priceCurrency / priceCoin;
			  $( '#apuspayments-total-blockchain' ).text( ' = ' + wc_apuspayments_params.order_currency_symbol.toString() + parseFloat(priceCurrency).toFixed(2) + ' = ' + parseFloat(priceParsed).toFixed(8) +  ' ' + blockchain.toString());
			});
		}

		/**
		 * Add error message
		 *
		 * @param {string} error
		 */
		function apusPaymentsAddErrorMessage( error ) {
			var wrapper = $( '#apuspayments-card-number-form' );

			$( '.woocommerce-error', wrapper ).remove();
			wrapper.prepend( '<div class="woocommerce-error" style="margin-bottom: 0.5em !important;">' + error + '</div>' );
		}

		/**
		 * Initialize the payment form.
		 */
		function apusPaymentsInitPaymentForm() {
			$( '#apuspayments-payment-form' ).show();

			$( '#apuspayments-card-blockchain' ).selectWoo({width: '100%'}).change(function() {
				apusPaymentsGetPriceText($( "option:selected", this ).data( 'blockchain-name' ), $( this ).val())
			});
			$( '#apuspayments-payment-form' ).card({
			    container: '.card-wrapper',
			    formSelectors: {
			        numberInput: 'input#apuspayments-card-number'
			    },
				placeholders: {
					number: '•••• •••• •••• ••••',
				}
			});
		}

		$( 'body' ).on( 'updated_checkout', function() {
			return apusPaymentsInitPaymentForm();
		});

		apusPaymentsInitPaymentForm();

	});

}( jQuery ));
