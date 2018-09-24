<?php
/**
 * WooCommerce ApusPayments API class
 *
 * @package WooCommerce_ApusPayments/Classes/API
 * @version 2.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce ApusPayments API.
 */
class WC_ApusPayments_API {

	/**
	 * Gateway class.
	 *
	 * @var WC_ApusPayments_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param WC_ApusPayments_Gateway $gateway Payment Gateway instance.
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get the API environment.
	 *
	 * @return string
	 */
	protected function get_environment() {
		return ( 'yes' == $this->gateway->sandbox ) ? 'sandbox.' : 'api.';
	}

	/**
	 * Get the checkout URL.
	 *
	 * @return string.
	 */
	public function get_checkout_url() {
		return 'https://' . $this->get_environment() . 'apuspayments.com/v1/checkout';
	}

	/**
	 * Get the checkout recurrent URL.
	 *
	 * @return string.
	 */
	protected function get_checkout_recurrent_url() {
		return 'https://' . $this->get_environment() . 'apuspayments.com/v1/checkout/recurrent';
	}


	/**
	 * Get the payment URL.
	 *
	 * @param  string $vendorKey Vendor Key.
	 *
	 * @return string.
	 */
	protected function get_payments_url( $vendorKey ) {
		return 'https://' . $this->get_environment() . 'apuspayments.com/v1/checkout/' . $vendorKey;
	}

	/**
	 * Get the currencies URL.
	 *
	 * @return string.
	 */
	protected function get_currencies_url( $vendorKey ) {
		return 'https://' . $this->get_environment() . 'apuspayments.com/v1/coin/currency/' . $vendorKey;
	}

	/**
	 * Get the blockchain URL.
	 *
	 * @return string.
	 */
	protected function get_blockchain_url( $vendorKey ) {
		return 'https://' . $this->get_environment() . 'apuspayments.com/v1/coin/blockchain/' . $vendorKey;
	}

	/**
	 * Check if is localhost.
	 *
	 * @return bool
	 */
	protected function is_localhost() {
		$url  = home_url( '/' );
		$home = untrailingslashit( str_replace( array( 'https://', 'http://' ), '', $url ) );

		return in_array( $home, array( 'localhost', '127.0.0.1' ) );
	}

	/**
	 * Money format.
	 *
	 * @param  int/float $value Value to fix.
	 *
	 * @return float            Fixed value.
	 */
	protected function money_format( $value ) {
		return number_format( $value, 2, '.', '' );
	}

	/**
	 * Sanitize the item description.
	 *
	 * @param  string $description Description to be sanitized.
	 *
	 * @return string
	 */
	protected function sanitize_description( $description ) {
		return sanitize_text_field( substr( $description, 0, 95 ) );
	}

	/**
	 * Get error message.
	 *
	 * @param  int $code Error code.
	 *
	 * @return string
	 */
	public function get_error_message( $code ) {
		$code = (string) $code;

		$messages = array(
			'001' => __( 'Invalid parameters' ),
			'002' => __( 'Failed to connect to the database' ),
			'003' => __( 'Database operation failed' ),
			'004' => __( 'Vendor key not found' ),
			'005' => __( 'Error checking balance' ),
			'006' => __( 'Insufficient funds' ),
			'007' => __( 'Failed to store transaction' ),
			'008' => __( 'Failed to fetch buyer' ),
			'009' => __( 'Buyer not found by card number' ),
			'010' => __( 'Failed to fetch merchant' ),
			'011' => __( 'Transaction not approved' ),
			'012' => __( 'Module (blockchain) not available' ),
			'013' => __( 'Invalid password' ),
			'014' => __( 'Buyer does not have wallet' ),
			'015' => __( 'Merchant does not have wallet' ),
			'016' => __( 'Failed to send email' ),
			'017' => __( 'Approved transaction' ),
			'018' => __( 'Failed to store schedule' ),
			'019' => __( 'Successful payment and scheduling' ),
			'020' => __( 'Successful scheduling' ),
			'021' => __( 'Query performed successfully' ),
		);

		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}

		return __( 'An error has occurred while processing your payment, please review your data and try again. Or contact us for assistance.', 'woocommerce-apuspayments' );
	}

	/**
	 * Do requests in the ApusPayments API.
	 *
	 * @param  string $url      URL.
	 * @param  string $method   Request method.
	 * @param  array  $data     Request data.
	 * @param  array  $headers  Request headers.
	 *
	 * @return array            Request response.
	 */
	protected function do_request( $url, $method = 'POST', $data = array(), $headers = array() ) {
		$params = array(
			'method'  => $method,
			'timeout' => 60,
		);

		if ( 'POST' == $method && ! empty( $data ) ) {
			$params['body'] = $data;
		}

		if ( ! empty( $headers ) ) {
			$params['headers'] = $headers;
		}

		return wp_safe_remote_post( $url, $params );
	}

	/**
	 * Get order items.
	 *
	 * @param  WC_Order $order Order data.
	 *
	 * @return array           Items list, extra amount and shipping cost.
	 */
	protected function get_order_item( $order ) {
		$extra_amount  = 0;
		$shipping_cost = 0;

		return array(
			'description' 	=> $this->sanitize_description( sprintf( __( 'Order %s', 'woocommerce-apuspayments' ), $order->get_order_number() ) ),
			'amount'      	=> $this->money_format( $order->get_total() ),
			'quantity'    	=> 1,
			'extra_amount'  => $extra_amount,
			'shipping_cost' => $shipping_cost,
		);
	}

	/**
	 * Get the direct payment xml.
	 *
	 * @param WC_Order $order Order data.
	 * @param array    $posted Posted data.
	 *
	 * @return string
	 */
	protected function get_payment_request( $order, $posted ) {
		$item   = $this->get_order_item( $order );
		$blockchain  = $posted['apuspayments-blockchain'];
		$currency  = get_woocommerce_currency();
		$cardNumber = hash('sha256', str_replace(' ', '', $posted['apuspayments-card-number']));
		$cardPassword = 'c66f1f34f49381e467d3abd43c77947f5d1dd362fd0eec6c2c1f27233ae9adf9';//hash('sha256', $posted['apuspayments-card-password']);
		$orderId = $order->get_id();
		$checkout = array(
			'pan' => $cardNumber,
			'password' => $cardPassword,
			'blockchain' => $blockchain,
			'amount' => $item['amount'],
			'currency' => $currency,
			'vendorKey' => $this->gateway->get_vendor_key()
		);

		return array(
			'item' => $item,
			'blockchain' => $blockchain,
			'currency' => $currency,
			'cardNumber' => $cardNumber,
			'cardPassword' => $cardPassword,
			'orderId' => $orderId,
			'checkout' => $checkout
		);
	}

	/**
	 * Do payment request.
	 *
	 * @param  WC_Order $order  Order data.
	 * @param  array    $posted Posted data.
	 *
	 * @return array
	 */
	public function do_payment_request( $order, $posted ) {
		/**
		 * Validate data posted to make payment.
		 */
		if ( ! $posted['apuspayments-card-number'] || strlen($posted['apuspayments-card-number']) < 8 ) {
			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( '<strong>' . __( 'ApusPayments', 'woocommerce-apuspayments' ) . '</strong>: ' .  __( 'Please, inform a valid card number.', 'woocommerce-apuspayments' ) ),
			);
		}

		if ( ! $posted['apuspayments-card-password'] ) {
			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( '<strong>' . __( 'ApusPayments', 'woocommerce-apuspayments' ) . '</strong>: ' .  __( 'Please, inform your card password.', 'woocommerce-apuspayments' ) ),
			);
		}

		if ( ! $posted['apuspayments-blockchain'] ) {
			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( '<strong>' . __( 'ApusPayments', 'woocommerce-apuspayments' ) . '</strong>: ' .  __( 'Please, select a blockchain to process the payment.', 'woocommerce-apuspayments' ) ),
			);
		}

		// get payment data
		$data = $this->get_payment_request( $order, $posted );

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting payment for order ' . $order->get_order_number() . ' with the following data: ' . $data );
		}

		$url      = $this->get_checkout_url();
		$response = $this->do_request( $url, 'POST', $data['checkout'], array( 'Content-Type' => 'application/x-www-form-urlencoded' ) );
		$body	  = json_decode($response['body']);

		if ( $body->status->code == '017' ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, $body->status->message . ' TXID: ' . $body->transaction->txId );
			}

			return array(
				'url'   => $this->gateway->get_return_url( $order ),
				'data'  => $body,
				'error' => '',
			);

		} else {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, $body->status->code . ' - ' . $body->status->message );
			}

			if ( $message = $this->get_error_message( $body->status->code ) ) {
				$errors[] = '<strong>' . __( 'ApusPayments', 'woocommerce-apuspayments' ) . '</strong>: ' . $message;
			}

			return array(
				'url'   => '',
				'data'  => '',
				'error' => $errors,
			);
		}

		// Return error message.
		return array(
			'url'   => '',
			'data'  => '',
			'error' => array( '<strong>' . __( 'ApusPayments', 'woocommerce-apuspayments' ) . '</strong>: ' . __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'woocommerce-apuspayments' ) ),
		);
	}

	/**
	 * Get currencies avaiables.
	 *
	 * @return array
	 */
	public function get_currencies_request() {
		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting currencies avaiables...' );
		}
		
		$vendorKey = $this->gateway->get_vendor_key();
		
		$url = $this->get_currencies_url( $vendorKey );

		$response = $this->do_request( $url, 'GET' );

		if ( isset( $response['body'] ) ) {
			$body = json_decode($response['body']);

			if ( $body->status->code == '021' ) {
				return array(
					'url'   => $url,
					'vendorKey' => $vendorKey,
					'data' => $body->data
				);
			}

			return array(
				'url'   => '',
				'vendorKey' => $vendorKey,
				'error' => $body->status->code . ' - ' . $body->status->message,
				'data' => array()
			);
		}

		return array(
			'url'   => '',
			'vendorKey' => $vendorKey,
			'error' => 'Failed to fetch currencies list',
			'data' => array()
		);
	}

	/**
	 * Get blockchains avaiables.
	 *
	 * @return array
	 */
	public function get_blockchains_request() {
		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting blockchain avaiables...' );
		}
		
		$vendorKey = $this->gateway->get_vendor_key();
		
		$url = $this->get_blockchain_url( $vendorKey );

		$response = $this->do_request( $url, 'GET' );

		if ( isset( $response['body'] ) ) {
			$body = json_decode($response['body']);

			if ( $body->status->code == '021' ) {
				return array(
					'url'   => $url,
					'vendorKey' => $vendorKey,
					'data' => $body->data,
				);
			}

			return array(
				'url'   => '',
				'vendorKey' => $vendorKey,
				'error' => $body->status->code . ' - ' . $body->status->message,
				'data' => array()
			);
		}

		return array(
			'url'   => '',
			'vendorKey' => $vendorKey,
			'error' => 'Failed to fetch blockchain list',
			'data' => array()
		);
	}
}
