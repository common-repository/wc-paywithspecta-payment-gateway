<?php

/**
 * Fired during plugin activation.
 *
 * contains functions that will run woocommerce activities.
 *
 * @since      1.0.0
 * @package    Wc_Paywithspecta_Payment_Gateway
 * @subpackage Wc_Paywithspecta_Payment_Gateway/includes
 * @author     Acesys Solutions <info@acesys.com.ng>
 */

function acesys_paywithspecta_wc_init()
{

	if (!class_exists('WC_Payment_Gateway')) {
		add_action('admin_notices', 'acesys_paywithspecta_wc_missing_notice');
		return;
	}
	add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), 'acesys_wc_gateway_paywithspecta_action_links' );
	add_action( 'admin_notices', 'acesys_paywithspecta_testmode_notice' );
	require_once dirname(__FILE__) . '/class-wc-gateway-paywithspecta.php';
	add_filter('woocommerce_payment_gateways', 'paywithspecta_add_gateway_class');
	add_filter('woocommerce_available_payment_gateways', 'paywithspecta_gateway_by_country_amount');
	
}

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function acesys_wc_gateway_paywithspecta_action_links( $links ) {
	
	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paywithspecta' ) . '" title="View PayWithSpecta WooCommerce Settings">Settings</a>',
	);

	return array_merge( $settings_link, $links );

}

/**
 * Add PayWithSpecta Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function paywithspecta_add_gateway_class($methods)
{
	$methods[] = 'WC_Gateway_PayWithSpecta';
	return $methods;
}
/**
 * Display a notice if WooCommerce is not installed
 */
function acesys_paywithspecta_wc_missing_notice()
{
	echo '<div class="error"><p><strong>' . sprintf(__('PayWithSpecta requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'wc-paywithspecta-payment-gateway'), '<a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') . '" class="thickbox open-plugin-details-modal">here</a>') . '</strong></p></div>';
}
/**
 * Display the test mode notice.
 **/
function acesys_paywithspecta_testmode_notice() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$paywithspecta_settings = get_option('woocommerce_paywithspecta_settings');
	$test_mode = isset($paywithspecta_settings['testmode']) ? $paywithspecta_settings['testmode'] : '';

	if ( 'yes' === $test_mode ) {
		/* translators: 1. Paystack settings page URL link. */
		echo '<div class="error"><p>' . sprintf( __( 'PayWithSpecta test mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site.', 'wc-paywithspecta-payment-gateway' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paywithspecta' ) ) ) . '</p></div>';
	}
}
function paywithspecta_gateway_by_country_amount($gateways)
{
	$logger = wc_get_logger();
	if (is_admin()) {
		return $gateways;
	}

	$paywithspecta_settings = get_option('woocommerce_paywithspecta_settings');
	$min_amount = isset($paywithspecta_settings['min_amount']) ? floatval($paywithspecta_settings['min_amount']) : 20000.00;

	if (is_wc_endpoint_url('order-pay')) { // Pay for order page
		$order = wc_get_order(wc_get_order_id_by_order_key($_GET['key']));
		$country = $order->get_billing_country();

		$amount = floatval($order->get_total());
	} else { // Cart page
		global $woocommerce;
		if ( ! WC()->cart->prices_include_tax ) {
			$amount = WC()->cart->cart_contents_total;
		} else {
			$amount = WC()->cart->cart_contents_total + WC()->cart->tax_total;
		}

		$country = WC()->customer->get_billing_country();
	}

	$currency = get_woocommerce_currency();
	
	if ('NG' !== $country) {
		if (isset($gateways['paywithspecta'])) {
			unset($gateways['paywithspecta']);
		}
	} else if ($amount < $min_amount) {
		if (isset($gateways['paywithspecta'])) {
			unset($gateways['paywithspecta']);
		}
	}else if (strtolower($currency) != "ngn") {
		if (isset($gateways['paywithspecta'])) {
			unset($gateways['paywithspecta']);
		}
	}

	return $gateways;
}

add_action('plugins_loaded', 'acesys_paywithspecta_wc_init', 99);
