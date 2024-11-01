<?php

/**
 *
 * @link              https://www.acesys.com.ng
 * @since             1.0.1
 * @package           Wc_Paywithspecta_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Payment Gateway for PayWithSpecta
 * Plugin URI:        https://www.acesys.com.ng/paywithspecta
 * Description:       Payment Gateway for PayWithSpecta allows your buyers to pay for goods and services instalmentally while you receive you payments in full instantly.
 * Version:           1.0.1
 * Author:            Acesys Solutions
 * Author URI:        https://www.acesys.com.ng
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-paywithspecta-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WC_PAYWITHSPECTA_PAYMENT_GATEWAY_VERSION', '1.0.1' );
define( 'PAYWITHSPECTA_MAIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-paywithspecta-payment-gateway-activator.php
 */
function activate_wc_paywithspecta_payment_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-paywithspecta-payment-gateway-activator.php';
	Wc_Paywithspecta_Payment_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-paywithspecta-payment-gateway-deactivator.php
 */
function deactivate_wc_paywithspecta_payment_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-paywithspecta-payment-gateway-deactivator.php';
	Wc_Paywithspecta_Payment_Gateway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wc_paywithspecta_payment_gateway' );
register_deactivation_hook( __FILE__, 'deactivate_wc_paywithspecta_payment_gateway' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-paywithspecta-payment-gateway.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wc_paywithspecta_payment_gateway() {

	$plugin = new Wc_Paywithspecta_Payment_Gateway();
	$plugin->run();

}
run_wc_paywithspecta_payment_gateway();
