<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.acesys.com.ng
 * @since      1.0.1
 *
 * @package    Wc_Paywithspecta_Payment_Gateway
 * @subpackage Wc_Paywithspecta_Payment_Gateway/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.1
 * @package    Wc_Paywithspecta_Payment_Gateway
 * @subpackage Wc_Paywithspecta_Payment_Gateway/includes
 * @author     Acesys Solutions <info@acesys.com.ng>
 */
class Wc_Paywithspecta_Payment_Gateway_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-paywithspecta-payment-gateway',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
