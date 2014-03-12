<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Harvey Kane <code@ragepank.com>
 * Copyright 2008 Michael Holt <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

/* Define the class for the cart */
if (!defined('Jojo_Cart_Class')) {
    define('Jojo_Cart_Class', Jojo::getOption('jojo_cart_class', 'jojo_plugin_jojo_cart'));
}

if (class_exists(Jojo_Cart_Class)) {
    call_user_func(array(Jojo_Cart_Class, 'setPaymentHandler'), 'jojo_plugin_jojo_cart_dps_pxpay');
}

$_options[] = array(
    'id'          => 'dps_currencies',
    'category'    => 'Cart',
    'label'       => 'DPS Currencies',
    'description' => 'A comma separated list of currencies supported by DPS. Multi-currency is currently only supported via BNZ bank, and it must be pre-arranged with them.',
    'type'        => 'text',
    'default'     => 'NZD',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_card_types',
    'category'    => 'Cart',
    'label'       => 'DPS Card types',
    'description' => 'A comma separated list of card types that are accepted by DPS (visa, mastercard, amex, diners)',
    'type'        => 'text',
    'default'     => 'visa,mastercard',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_transaction_type',
    'category'    => 'Cart',
    'label'       => 'DPS Transaction types',
    'description' => 'A comma separated list of card types that are accepted by DPS (visa, mastercard, amex, diners)',
    'type'        => 'radio',
    'default'     => 'Purchase',
    'options'     => 'Purchase,Auth,Complete,Refund,Validate',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_version',
    'category'    => 'Cart',
    'label'       => 'DPS PxPay Version',
    'description' => '',
    'type'        => 'radio',
    'default'     => '1',
    'options'     => '1,2',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_username',
    'category'    => 'Cart',
    'label'       => 'DPS username',
    'description' => 'The username provided by DPS',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_test_username',
    'category'    => 'Cart',
    'label'       => 'DPS TEST username',
    'description' => 'The test username provided by DPS, used when debugging the payment system',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);

$_options[] = array(
    'id'          => 'dps_password',
    'category'    => 'Cart',
    'label'       => 'DPS password',
    'description' => 'The password provided by DPS',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);


$_options[] = array(
    'id'          => 'dps_test_password',
    'category'    => 'Cart',
    'label'       => 'DPS TEST password',
    'description' => 'The test password provided by DPS, used when debugging the payment system',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_dps'
);