<?php
/**
 * WooCommerce ApusPayments Gateway class
 *
 * @package WooCommerce_ApusPayments/Classes/Gateway
 * @version 2.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce ApusPayments gateway.
 */
class WC_ApusPayments_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'apuspayments';
		$this->icon               = apply_filters( 'woocommerce_apuspayments_icon', plugins_url( 'assets/images/apuspayments.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title       = __( 'ApusPayments', 'woocommerce-apuspayments' );
		$this->method_description = __( 'Allow customers to easily checkout using criptocurrency.', 'woocommerce-apuspayments' );
		$this->order_button_text  = __( 'Proceed to payment', 'woocommerce-apuspayments' );

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title             = $this->get_option( 'title' );
		$this->description       = $this->get_option( 'description' );
		$this->vendorKey         = $this->get_option( 'vendorKey' );
		$this->blockchain        = $this->get_option( 'blockchain' );
		$this->sandbox           = $this->get_option( 'sandbox', 'no' );
		$this->debug             = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$this->log = wc_get_logger();
			} else {
				$this->log = new WC_Logger();
			}
		}

		// Set the API.
		$this->api = new WC_ApusPayments_API( $this );

		// Load the form fields.
		$this->init_form_fields();

		// Main actions.
		add_action( 'woocommerce_api_wc_apuspayments_gateway', array( $this, 'ipn_handler' ) );
		add_action( 'valid_apuspayments_ipn_request', array( $this, 'update_order_status' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );
	}

	/**
	 * Returns a array with symbol of supported blockchains.
	 *
	 * @return array
	 */
	public function get_supported_blockchains() {
		return $this->api->get_blockchains_request();
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		$response = $this->api->get_currencies_request();

		$currencies = array();

		foreach ($response['data'] as $currency) {
			$currencies[] = $currency->symbol;
		}

		return in_array(get_woocommerce_currency(), $currencies);
	}

	/**
	 * Returns a bool that indicates if any blockchain is setted as payment method.
	 *
	 * @return bool
	 */
	public function has_enable_any_blockchain() {
		return count( $this->get_blockchains() ) > 0;
	}

	/**
	 * Get vendorKey.
	 *
	 * @return string
	 */
	public function get_vendor_key() {
		return $this->vendorKey;
	}

	/**
	 * Get blockchain.
	 *
	 * @return string
	 */
	public function get_blockchains() {
		return $this->blockchain;
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = 'yes' === $this->get_option( 'enabled' ) && '' !== $this->get_vendor_key() && $this->using_supported_currency() && $this->has_enable_any_blockchain();

		return $available;
	}

	/**
	 * Checkout scripts.
	 */
	public function checkout_scripts() {
		if ( is_checkout() && $this->is_available() ) {
			if ( ! get_query_var( 'order-received' ) ) {
				wp_enqueue_style( 'apuspayments-checkout', plugins_url( 'assets/css/frontend/transparent-checkout.min.css', plugin_dir_path( __FILE__ ) ), array(), WC_APUSPAYMENTS_VERSION );
				wp_enqueue_script( 'apuspayments-checkout', plugins_url( 'assets/js/frontend/transparent-checkout.min.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_APUSPAYMENTS_VERSION, true );
				wp_enqueue_script( 'apuspayments-card-plugin', plugins_url( 'assets/js/frontend/jquery.card.min.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_APUSPAYMENTS_VERSION, true );

				wp_localize_script(
					'apuspayments-checkout',
					'wc_apuspayments_params',
					array(
						'order_total_price'      => $this->get_order_total(),
						'order_currency'  	     => get_woocommerce_currency(),
						'order_currency_symbol'  => get_woocommerce_currency_symbol(),
						'general_error'     	 => __( 'Unable to process the data from your card on the ApusPayments, please try again or contact us for assistance.', 'woocommerce-apuspayments' ),
					)
				);
			}
		}
	}

	/**
	 * Get log.
	 *
	 * @return string
	 */
	protected function get_log_view() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-apuspayments' ) . '</a>';
		}

		return '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>';
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$options = array();

		$blockchains = $this->get_supported_blockchains();

		foreach ($blockchains['data'] as $blockchain) {
			$options[$blockchain->abbreviation] = $blockchain->abbreviation . ' - ' . $blockchain->name;
		}

		$this->form_fields = array(
			'enabled'              => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-apuspayments' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable ApusPayments', 'woocommerce-apuspayments' ),
				'default' => 'yes',
			),
			'title'                => array(
				'title'       => __( 'Title', 'woocommerce-apuspayments' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-apuspayments' ),
				'desc_tip'    => true,
				'default'     => __( 'ApusPayments', 'woocommerce-apuspayments' ),
			),
			'description'          => array(
				'title'       => __( 'Description', 'woocommerce-apuspayments' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-apuspayments' ),
				'default'     => __( 'Pay using cryptocurrency', 'woocommerce-apuspayments' ),
			),
			'integration'          => array(
				'title'       => __( 'Integration', 'woocommerce-apuspayments' ),
				'type'        => 'title',
				'description' => '',
			),
			'sandbox'              => array(
				'title'       => __( 'Sandbox', 'woocommerce-apuspayments' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable <b>testnet</b> server?', 'woocommerce-apuspayments' ),
				'desc_tip'    => true,
				'default'     => 'no',
				'description' => __( 'ApusPayments sandbox can be used to test the payments at testnet network.', 'woocommerce-apuspayments' ),
			),
			'vendorKey'                => array(
				'title'       => __( 'VendorKey', 'woocommerce-apuspayments' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Please enter your vendorKey. This is needed to process the payments and notifications. Is possible generate a new keys %s.', 'woocommerce-apuspayments' ), '<a href="https://docs.apuspayments.com">' . __( 'see our documentation', 'woocommerce-apuspayments' ) . '</a>' ),
				'default'     => '',
			),
			'blockchain'      => array(
				'title'       => __( 'Blockchains', 'woocommerce-apuspayments' ),
				'type' 		  => 'multiselect',
				'description' => __( 'Choose which blockchains will be accepted as payment method', 'woocommerce-apuspayments' ),
				'desc_tip'    => true,
				'default'     => '',
				'class'       => 'wc-enhanced-select',
				'options'     => $options,
			),
			'debug'                => array(
				'title'       => __( 'Debug Log', 'woocommerce-apuspayments' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-apuspayments' ),
				'default'     => 'no',
				/* translators: %s: log page link */
				'description' => sprintf( __( 'Log events, such as API requests, inside %s', 'woocommerce-apuspayments' ), $this->get_log_view() ),
			)
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'apuspayments-admin', plugins_url( 'assets/js/admin/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_APUSPAYMENTS_VERSION, true );

		include dirname( __FILE__ ) . '/admin/views/html-admin-page.php';
	}

	/**
	 * Send email notification.
	 *
	 * @param string $subject Email subject.
	 * @param string $title   Email title.
	 * @param string $message Email message.
	 */
	protected function send_email( $subject, $title, $message ) {
		$mailer = WC()->mailer();

		$mailer->send( get_option( 'admin_email' ), $subject, $mailer->wrap_message( $title, $message ) );
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );

		$description = $this->get_description();

		if ( $description ) {
			echo wpautop( wptexturize( $description ) ); // WPCS: XSS ok.
		}

		$cart_total = $this->get_order_total();

		wc_get_template(
			'checkout-form.php', array(
				'cart_total'  => $cart_total,
			), 'woocommerce/apuspayments/', WC_ApusPayments::get_templates_path()
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$response = $this->api->do_payment_request( $order, $_POST );

		$data = $response['data'];

		if ( $data->transaction->txId ) {
			$this->update_order_status( $order, $data );

			return array(
				'result'   => 'success',
				'redirect' => $response['url']
			);
		} else {
			wc_add_notice( $data->status->message, 'error' );

			return array(
				'result'   => 'fail',
				'redirect' => ''
			);
		}
	}

	/**
	 * Update order status.
	 *
	 * @param array $posted ApusPayments post data.
	 */
	public function update_order_status( $order, $response ) {
		if ( isset( $order->order_key ) ) {
			// Checks whether the invoice number matches the order.
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'ApusPayments payment status for order ' . $order->get_order_number() . ' is: ' . intval( $order->status ) );
			}

			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

			// Save meta data.
			$this->save_payment_meta_data( $order, $response );

			switch ( $order->get_status() ) {
				case 'pending':
					if ( method_exists( $order, 'get_status' ) && 'cancelled' === $order->get_status() ) {
						$order->update_status( 'processing', __( 'ApusPayments: Payment approved.', 'woocommerce-apuspayments' ) );
						wc_reduce_stock_levels( $order_id );
					} else {
						try {
							$order->add_order_note( __( 'ApusPayments: Payment approved.', 'woocommerce-apuspayments' ) );
							$order->payment_complete( $response->transaction->txId );							
						} catch ( Exception $e ) {
							die($e->getMessage());
						}
					}

					break;
				case 5:
					$order->update_status( 'on-hold', __( 'ApusPayments: Payment came into dispute.', 'woocommerce-apuspayments' ) );
					$this->send_email(
						/* translators: %s: order number */
						sprintf( __( 'Payment for order %s came into dispute', 'woocommerce-apuspayments' ), $order->get_order_number() ),
						__( 'Payment in dispute', 'woocommerce-apuspayments' ),
						/* translators: %s: order number */
						sprintf( __( 'Order %s has been marked as on-hold, because the payment came into dispute in ApusPayments.', 'woocommerce-apuspayments' ), $order->get_order_number() )
					);

					break;
				case 'approved':
					$order->update_status( 'refunded', __( 'ApusPayments: Payment refunded.', 'woocommerce-apuspayments' ) );

					$this->send_email(
						/* translators: %s: order number */
						sprintf( __( 'Payment for order %s refunded', 'woocommerce-apuspayments' ), $order->get_order_number() ),
						__( 'Payment refunded', 'woocommerce-apuspayments' ),
						/* translators: %s: order number */
						sprintf( __( 'Order %s has been marked as refunded by ApusPayments.', 'woocommerce-apuspayments' ), $order->get_order_number() )
					);

					if ( function_exists( 'wc_increase_stock_levels' ) ) {
						wc_increase_stock_levels( $order_id );
					}

					break;
				case 'approved':
					$order->update_status( 'cancelled', __( 'ApusPayments: Payment canceled.', 'woocommerce-apuspayments' ) );

					if ( function_exists( 'wc_increase_stock_levels' ) ) {
						wc_increase_stock_levels( $order_id );
					}

					break;

				default:
					break;
			}
		}
	}

	/**
	 * Save payment meta data.
	 *
	 * @param WC_Order $order Order instance.
	 * @param array    $response Response API data.
	 */
	protected function save_payment_meta_data( $order, $response ) {
		$meta_data    = array();
		$payment_data = array(
			'type'         => '',
			'method'       => '',
			'installments' => '',
			'link'         => '',
		);

		if ( isset( $response->buyer->email ) ) {
			$meta_data[ __( 'Payer Email', 'woocommerce-apuspayments' ) ] = sanitize_text_field( (string) $response->buyer->email );
		}
		if ( isset( $response->buyer->name ) ) {
			$meta_data[ __( 'Payer Name', 'woocommerce-apuspayments' ) ] = sanitize_text_field( (string) $response->buyer->name );
		}		
		if ( isset( $response->coin->name ) ) {
			$meta_data[ __( 'Payment Blockchain', 'woocommerce-apuspayments' ) ] = $response->coin->name;
		}
		if ( isset( $response->coin->amount ) ) {
			$meta_data[ __( 'Payments Amount', 'woocommerce-apuspayments' ) ] = $response->coin->amount;
		}
		if ( isset( $response->coin->fee ) ) {
			$meta_data[ __( 'Payment Fee', 'woocommerce-apuspayments' ) ] = $response->coin->fee;
		}
		if ( isset( $response->transaction->txId ) ) {
			$meta_data[ __( 'Payment TxID', 'woocommerce-apuspayments' ) ] = $response->transaction->txId;
		}

		$meta_data['_wc_apuspayments_payment_data'] = $payment_data;

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'update_meta_data' ) ) {
			foreach ( $meta_data as $key => $value ) {
				$order->update_meta_data( $key, $value );
			}
			$order->save();
		} else {
			foreach ( $meta_data as $key => $value ) {
				update_post_meta( $order->id, $key, $value );
			}
		}
	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function receipt_page( $order_id ) {
		$order        = wc_get_order( $order_id );

		$request_data = $_POST;  // WPCS: input var ok, CSRF ok.
		
		if ( isset( $_GET['use_shipping'] ) && true === (bool) $_GET['use_shipping'] ) {  // WPCS: input var ok, CSRF ok.
			$request_data['ship_to_different_address'] = true;
		}

		$response = $this->api->do_checkout_request( $order, $request_data );

		include dirname( __FILE__ ) . '/views/html-receipt-page-error.php';
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			$data = $order->get_meta( '_wc_apuspayments_payment_data' );
		} else {
			$data = get_post_meta( $order->id, '_wc_apuspayments_payment_data', true );
		}

		if ( isset( $data['type'] ) ) {
			wc_get_template(
				'payment-instructions.php', array(
					'type'         => $data['type'],
					'link'         => $data['link'],
					'method'       => $data['method'],
					'installments' => $data['installments'],
				), 'woocommerce/apuspayments/', WC_ApusPayments::get_templates_path()
			);
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  WC_Order $order         Order object.
	 * @param  bool     $sent_to_admin Send to admin.
	 * @param  bool     $plain_text    Plain text or HTML.
	 * @return string
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			if ( $sent_to_admin || 'on-hold' !== $order->get_status() || $this->id !== $order->get_payment_method() ) {
				return;
			}

			$data = $order->get_meta( '_wc_apuspayments_payment_data' );
		} else {
			if ( $sent_to_admin || 'on-hold' !== $order->status || $this->id !== $order->payment_method ) {
				return;
			}

			$data = get_post_meta( $order->id, '_wc_apuspayments_payment_data', true );
		}

		if ( isset( $data['type'] ) ) {
			if ( $plain_text ) {
				wc_get_template(
					'emails/plain-instructions.php', array(
						'type'         => $data['type'],
						'link'         => $data['link'],
						'method'       => $data['blockchain'],
						'installments' => $data['installments'],
					), 'woocommerce/apuspayments/', WC_ApusPayments::get_templates_path()
				);
			} else {
				wc_get_template(
					'emails/html-instructions.php', array(
						'type'         => $data['type'],
						'link'         => $data['link'],
						'method'       => $data['blockchain'],
						'installments' => $data['installments'],
					), 'woocommerce/apuspayments/', WC_ApusPayments::get_templates_path()
				);
			}
		}
	}
}
