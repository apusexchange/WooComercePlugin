<?php
/**
 * Plugin Name:          WooCommerce ApusPayments
 * Plugin URI:           https://github.com/apuspayments/WooComercePlugin
 * Description:          Includes ApusPayments as a payment gateway to WooCommerce.
 * Author:               ApusPayments
 * Author URI:           https://apuspayments.com
 * Version:              2.13.1
 * License:              GPLv3 or later
 * Text Domain:          woocommerce-apuspayments
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      3.4.0
 *
 * WooCommerce ApusPayments is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * WooCommerce ApusPayments is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WooCommerce ApusPayments. If not, see
 * <https://www.gnu.org/licenses/gpl-3.0.txt>.
 *
 * @package WooCommerce_ApusPayments
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WC_APUSPAYMENTS_VERSION', '1.0.0' );
define( 'WC_APUSPAYMENTS_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'WC_APUSPAYMENTS' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-apuspayments.php';
	add_action( 'plugins_loaded', array( 'WC_APUSPAYMENTS', 'init' ) );
}
