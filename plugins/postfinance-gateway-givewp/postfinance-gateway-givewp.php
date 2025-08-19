<?php

use Thimoo\PostfinanceCheckoutFlex\CheckoutFlexGateway;

/*
 * Plugin Name: PostFinance Checkout Flex Gateway GiveWP
 * Plugin URI: https://github.com/compassionCH/wp-plugins/postfinance-gateway-givewp
 * Description: GiveWP gateway add-on compatible with PostFinance Checkout Flex platform.
 * Version: 1.0.0
 * Requires at least: 6.5.2
 * Requires PHP: 7.4
 * Author: Thimoo Sàrl <web@thimoo.ch>
 * Author URI: https://thimoo.ch
 * License: Apache License 2.0
 * License URI: https://opensource.org/licenses/Apache-2.0
 * Text Domain: postfinance-gateway-givewp
 * Domain Path: /languages
 * Requires Plugins: give/give.php
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/php-sdk/autoload.php';
require_once __DIR__.'/src/CheckoutFlexGateway.php';

// Register the gateway
add_action('givewp_register_payment_gateway', static function ($paymentGatewayRegister) {
    $paymentGatewayRegister->registerGateway(Thimoo\PostfinanceCheckoutFlex\CheckoutFlexGateway::class);
});

add_action('givewp_sync', 'givewp_sync_donations');
function givewp_sync_donations()
{
    $gateway = new CheckoutFlexGateway();
    $gateway->syncDonations();
}

if (! wp_next_scheduled('givewp_sync')) {
    wp_schedule_event(time(), 'hourly', 'givewp_sync');
}

/**
    GiveWP stores the submit button's text in a meta key in the database at form creation
    but this text is not translated correctly. The simplest way is to override the text for all forms
    The relevant code can be found here:
    https://github.com/impress-org/givewp/blob/e0251ee5d75decc223f38a741a4e8104b64d2e16/includes/forms/template.php#L2032-L2034
 */
add_filter('give_donation_form_submit_button_text', 'givewp_submit_button_override');

function givewp_submit_button_override($display_label_field)
{
    $lang = apply_filters('wpml_current_language', null);
    switch ($lang) {
        case 'de':
            return 'Jetzt spenden';
            break;
        case 'it':
            return 'Dona ora';
            break;
        default:
            return 'Donner maintenant';
            break;
    }

}
