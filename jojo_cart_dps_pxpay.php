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

define('_DPS_CURRENCY', 'NZD'); //Currently hard-coded to NZD

class jojo_plugin_jojo_cart_dps_pxpay extends JOJO_Plugin
{
    function getPaymentOptions()
    {
        /* ensure the order currency is the same as DPS currency */
        $currency = call_user_func(array(Jojo_Cart_Class, 'getCartCurrency'));
        if ($currency != _DPS_CURRENCY) return array();

        global $smarty;
        $options = array();

        /* get available card types (specified in options) */
        $cardtypes = explode(',', Jojo::getOption('dps_pxpay_card_types', 'visa,mastercard'));
        $cardimages = array();

        /* uppercase first letter of each card type */
        foreach ($cardtypes as $k => $v) {
            $cardtypes[$k] = trim(ucwords($v));
            if ($cardtypes[$k] == 'Visa') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardvisa.gif" alt="Visa" />';
            } elseif ($cardtypes[$k] == 'Mastercard') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardmastercard.gif" alt="Mastercard" />';
            } elseif ($cardtypes[$k] == 'Amex') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardamex.gif" alt="American Express" />';
            }
        }
        $smarty->assign('cardtypes', $cardtypes);
        $options[] = array('id' => 'dps', 'label' => 'Pay now by Credit card via secure payment provider DPS/payment express'.implode(', ', $cardimages), 'html' => $smarty->fetch('jojo_cart_dps_pxpay_checkout.tpl'));
        return $options;
    }

    /*
    * Determines whether this payment plugin is active for the current payment.
    */
    function isActive()
    {
        /* they submitted the form from the checkout page */
        if (Jojo::getFormData('handler', false) == 'dps_pxpay') return true;
        if (!isset($_GET['result'])) return false;

        /* Ensure the transaction has not already been processed - DPS may ping the script more than once */
        $token    = Jojo::getFormData('token', false);
        if ($token && isset($_GET['result'])) {
            $data = Jojo::selectQuery("SELECT * FROM {cart} WHERE token=? AND status='complete'", $token);
            if (count($data)) {
                /* redirect to thank you page if the transaction has been processed already */
                Jojo::redirect(_SECUREURL.'/cart/complete/'.$token.'/', 302);
            }
        }
        return true;

        /* Look for a post variable specifying DPS PX Pay, or a $_GET['result'] variable set by the DPS response */
        //$active = ((Jojo::getFormData('handler', false) == 'dps_pxpay') || isset($_GET['result'])) ? true : false;
    }

    function process()
    {
        define('DPS_URL', 'https://www.paymentexpress.com/pxpay/pxaccess.aspx');

        $cart     = call_user_func(array(Jojo_Cart_Class, 'getCart'));
        $testmode = call_user_func(array(Jojo_Cart_Class, 'isTestMode'));
        $token    = Jojo::getFormData('token', false);

        $errors  = array();

        /* ensure the order currency is the same as DPS currency */
        $currency = call_user_func(array(Jojo_Cart_Class, 'getCartCurrency'));
        if ($currency != _DPS_CURRENCY) {
            return array(
                        'success' => false,
                        'receipt' => '',
                        'errors'  => array('This plugin is only currently able to process transactions in '._DPS_CURRENCY.'.')
                        );
        }

        /* error checking */

        /* set DPS authentication constants, used in the DPS script */

        if ($testmode) {
            define('DPS_USERNAME', Jojo::getOption('dps_test_username', false));
            define('DPS_PASSWORD', Jojo::getOption('dps_test_password', false));
        } else {
            define('DPS_USERNAME', Jojo::getOption('dps_username', false));
            define('DPS_PASSWORD', Jojo::getOption('dps_password', false));
        }

        /* Ensure the transaction has not already been processed - DPS may ping the script more than once */
        $data = Jojo::selectQuery("SELECT * FROM {cart} WHERE token=? AND status='complete'", $token);
        if (count($data)) {
            /* redirect to thank you page if the transaction has been processed already */
            Jojo::redirect(_SECUREURL.'/cart/complete/'.$token.'/', 302);
        }

        /* include the PxPay functions */
        foreach (Jojo::listPlugins('external/PxPay_PHP_Curl/pxpay.inc') as $pluginfile) {
            require_once($pluginfile);
            break;
        }

        /* create PxPay object */
        $pxpay = new PxPay_Curl(DPS_URL, DPS_USERNAME, DPS_PASSWORD);

        /* check for $result data appended to querystring */
        $result = Jojo::getFormData('result', false); //'result' is the encrypted response from DPS

        if ($result) {
            /* This code is called by DPS directly to notify of a result. The user is also redirected back here, but they should be redirected to the thank you page before this code runs */
            $enc_hex = $result;
            #getResponse method in PxPay object returns PxPayResponse object
            #which encapsulates all the response data
            $rsp = $pxpay->getResponse($enc_hex);

            if ($rsp->getStatusRequired() == "1") {
                $errors[] = 'An error has occurred.';
            } elseif ($rsp->getSuccess() == "1") {
                //$result = "The transaction was approved.";
            } else {
                $errors[] = 'The transaction was declined.';
            }

            # the following are the fields available in the PxPayResponse object
            $Success           = ($rsp->getSuccess() == 1);   # =1 when request succeeds
            $Retry             = $rsp->getRetry();     # =1 when a retry might help
            $StatusRequired    = $rsp->getStatusRequired();      # =1 when transaction "lost"
            $AmountSettlement  = $rsp->getAmountSettlement();
            $AuthCode          = $rsp->getAuthCode();  # from bank
            $CardName          = $rsp->getCardName();  # e.g. "Visa"
            $DpsTxnRef	       = $rsp->getDpsTxnRef();

            # the following values are returned, but are from the original request
            $TxnType           = $rsp->getTxnType();
            $TxnData1          = $rsp->getTxnData1();
            $TxnData2          = $rsp->getTxnData2();
            $TxnData3          = $rsp->getTxnData3();
            $CurrencyInput     = $rsp->getCurrencyInput();
            $EmailAddress      = $rsp->getEmailAddress();
            $MerchantReference = $rsp->getMerchantReference();
            $ResponseText = $rsp->getResponseText();

            /* build receipt */
            $receipt = array('Transaction Amount' => $AmountSettlement,
                             'Auth Code'          => $AuthCode,
                             'Card Name'          => $CardName,
                             'DpsTxnRef'          => $DpsTxnRef,
                             'Email Address'      => $EmailAddress,
                             'Response'           => $ResponseText
                             );

            $message = ($Success) ? "Thank you for your payment via $CardName Credit Card.": '';

            return array(
                        'success' => $Success,
                        'receipt' => $receipt,
                        'errors'  => $errors,
                        'message' => $message
                        );

        } else {
            /* Prepare the request, send request to DPS, then redirect user to URL provided by DPS */
            $request = new PxPayRequest();
            //$amount    = number_format(call_user_func(array(Jojo_Cart_Class, 'total')), 2, '.', ''); //DPS amounts MUST be in the format '1.00'
            #Set up PxPayRequest Object
            $request->setAmountInput(number_format($cart->order['amount'], 2, '.', ''));
            $request->setTxnData1($cart->token);
            $request->setTxnData2('');
            $request->setTxnData3('');
            $request->setTxnType("Purchase");
            $request->setInputCurrency($cart->order['currency']);
            $request->setMerchantReference($cart->token);
            $request->setEmailAddress($cart->fields['Email']);
            $request->setUrlFail(_SECUREURL.'/cart/process/'.$cart->token.'/');
            $request->setUrlSuccess(_SECUREURL.'/cart/process/'.$cart->token.'/');

            #Call makeResponse of PxPay object to obtain the 3-DES encrypted payment request
            $request_string = $pxpay->makeRequest($request);

            $response = new MifMessage($request_string);
            $url = $response->get_element_text("URI");
            $valid = $response->get_attribute("valid");
            if ($valid == 1) {
               Jojo::redirect($url, 302);
            } else {
               echo $response;
               exit;
            }
        }
    }
}