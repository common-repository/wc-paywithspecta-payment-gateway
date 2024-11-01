<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_Gateway_PayWithSpecta
 */
class WC_Gateway_PayWithSpecta extends WC_Payment_Gateway
{
    /**
     * Class constructor, more about it in Step 3
     */
    public function __construct()
    {
        $this->id = 'paywithspecta'; // payment gateway plugin ID
        $this->icon = WC_HTTPS::force_https_url(plugins_url('assets/images/main_logo_medium.png', PAYWITHSPECTA_MAIN_FILE)); // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'Payment Gateway for PayWithSpecta';
        $this->method_description = sprintf(__('Boost sales with Specta Get paid instantly while Specta gives your customers a flexible payment option. Don\'t have a PayWithSpecta Merchant ID? <a href="%2$s" target="_blank">Sign up here</a> and <a href="%2$s" target="_blank">get your API Key</a>', 'paywithspecta'), 'https://paywithspecta.com/account/merchant-register', 'https://paywithspecta.com/app/merchant/settings');
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
        $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // We need custom JavaScript to obtain a token
        //add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        //add_action( 'woocommerce_api_wc_stripe', [ $this, 'check_for_webhook' ] );
        add_action('woocommerce_api_' . $this->id, array($this, 'webhook'));
    }

    /**
     * Plugin options, we deal with it in Step 3 too
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable Payment Gateway for PayWithSpecta',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'PayWithSpecta',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Using your Specta ID is the smartest, safest and most convenient way to make purchases and pay in instalments.',
            ),
            'min_amount' => array(
                'title'       => 'Minimun Order Amount',
                'type'        => 'number',
                'description' => 'The minimum order amount that will qualify for PayWithSpecta. Should not be less than 20,000 naira',
                'default'     => '20000',
            ),
            'testmode' => array(
                'title'       => 'Test mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Place the payment gateway in test mode using Test API key.',
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'merchantid' => array(
                'title'       => 'PayWithSpecta Merchant ID',
                'type'        => 'text'
            ),
            'test_private_key' => array(
                'title'       => 'Test API Key (use "TEST_API_KEY" without the quotes)',
                'type'        => 'text',
                'default'     => 'TEST_API_KEY',
            ),
            'private_key' => array(
                'title'       => 'Live API Key',
                'type'        => 'text'
            )
        );
    }

    /**
     * You will need it if you want your custom credit card form, Step 4 is about it
     */
    //public function payment_fields()
    //{
    //}

    /**
     * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
     */
    /*public function payment_scripts()
    {
    }*/

    /*
              * Fields validation, more in Step 5
             
    public function validate_fields()
    {
    }*/

    /**
     * We're processing the payments here, everything about it is in Step 5
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        $logger = wc_get_logger();
        // we need it to get any order detailes
        $order = wc_get_order($order_id);
        //get unique payment url
        try {
            $url = 'https://paywithspectaapi.sterling.ng/api/Purchase/CreatePaymentUrl';
            $paywithspecta_settings = get_option('woocommerce_paywithspecta_settings');
            $test_mode = isset($paywithspecta_settings['testmode']) ? $paywithspecta_settings['testmode'] : '';
            $merchant_id = isset($paywithspecta_settings['merchantid']) ? intval($paywithspecta_settings['merchantid']) : 0;
            $api_key = "";
            if ($test_mode == "yes") {
                $api_key = isset($paywithspecta_settings['test_private_key']) ? $paywithspecta_settings['test_private_key'] : "";
            } else {
                $api_key = isset($paywithspecta_settings['private_key']) ? $paywithspecta_settings['private_key'] : "";
            }


            $data = [
                "callBackUrl" => get_bloginfo('wpurl') . '/wc-api/paywithspecta/',
                "reference" => "$order_id",
                "merchantId" => $merchant_id,
                "description" => "Order #$order_id from " . get_bloginfo('name'),
                "amount" => $order->get_total()
            ];
            //$logger->debug("api key: $api_key", ['source' => "PatWithSpecta"]);
            //$logger->debug(wp_json_encode($data), ['source' => "PatWithSpecta"]);
            $response = wp_remote_post($url, array(
                'body'    => wp_json_encode($data),
                'headers' => array(
                    'x-ApiKey' => $api_key,
                    'Content-Type' => "application/json"
                ),
            ));
            $body = json_decode($response["body"], true);

            //$logger->debug("response from specta:= " .  wp_json_encode($body), ['source' => "PatWithSpecta"]);
            //$logger->debug($body["success"], ['source' => "PatWithSpecta"]);
            if (isset($body["success"])) {
                if ($body["success"] == false) {
                    $logger->debug("Error processing payment - [error code: " . $body["error"]["code"] . ", " . $body["error"]["message"] . ']', ['source' => "PatWithSpecta"]);
                    wc_add_notice("Something went wrong | error code : ".$body["error"]["code"]." | error message : ".$body["error"]["message"]."- Please contact Support", 'error');
                    return;
                } else {
                    $nurl = $body["result"];
                    return array(
                        'result' => 'success',
                        'redirect' => $nurl
                    );
                }
            }
            /* return array(
                'result' => 'success',
                'redirect' => $response
            ); */
        } catch (\Exception $e) {
            $logger->debug($e->getMessage(), ['source' => "PatWithSpecta"]);
        }
    }

    /**
     * In case you need a webhook, like PayPal IPN etc
     */
    public function webhook()
    {
        $logger = wc_get_logger();
        global $woocommerce;
        $url = 'https://paywithspectaapi.sterling.ng/api/purchase/verifypurchase';//?reference=' . urlencode($_GET["ref"]);
       
        $data = [
            "verificationToken" => $_GET["ref"]
        ];
        
        
        $response = wp_remote_post($url, array(
            'body'    => wp_json_encode($data),
            'headers' => array(
                'Content-Type' => "application/json",
                //'x-ApiKey' => $api_key
            )
        ));


        //$logger->debug($response["body"], ['source' => "PatWithSpecta"]);
        $body = json_decode($response["body"],true);
        if(isset($body["result"]["data"]["isSuccessful"])){
            
            $payref = $body["result"]["data"]["reference"];
            $order = wc_get_order($payref);
            if($body["result"]["data"]["isSuccessful"]==true){
                
                $order->payment_complete();
                $order->reduce_order_stock();
                $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
                $woocommerce->cart->empty_cart();
               
            }
        }else{
            $logger->debug('Something went wrong, <a href="'.get_bloginfo('wpurl').'">please go back to home page</a>', ['source' => "PatWithSpecta"]);
            
        }
        return wp_redirect($this->get_return_url( $order ));
        /* $order = wc_get_order(wp_json_encode($_GET['ref']));
        $order->payment_complete();
        $order->reduce_order_stock();

        update_option('webhook_debug', $_GET); */
        
    }
}
