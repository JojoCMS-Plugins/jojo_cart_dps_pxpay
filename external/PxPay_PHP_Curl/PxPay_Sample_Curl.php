<?
#******************************************************************************
#* Name          	: PxPay_Sample_Curl.php
#* Description   	: Direct Payment Solutions Payment Express PHP Sample
#* Copyright	 	: Direct Payment Solutions 2007(c)
#* Date          	: 2007-01-04
#* References    	: http://www.paymentexpress.com/blue.asp?id=d_pxpay
#*@version 			: 1.0
#******************************************************************************

# This file is a SAMPLE showing redirect to Payments Page from PHP.
#Inlcude PxAccess Objects
include "pxpay.inc";

  $PxPay_Url    = "https://www.paymentexpress.com/pxpay/pxaccess.aspx";
  $PxPay_Userid = "userid"; #Change to your user ID
  $PxPay_Key    =  "Encryptionkey"; #Your Encryption key from DPS

  #
  # MAIN
  #

  $pxpay = new PxPay_Curl( $PxPay_Url, $PxPay_Userid, $PxPay_Key );

  if (isset($_REQUEST["result"]))
  {
    # this is a redirection BACK from the Payments Page.
    print_result();
  }
  elseif (isset($_REQUEST["Submit"]))
  {
    # this is a form submission -- redirect to Payments Page.
    redirect_form();
  }
  else
  {
    # this is a fresh request -- show the purchase form.
    print_form();
  }


#******************************************************************************
# This function receives information back from the Payments Page,
# and displays it to the user.
#******************************************************************************
function print_result()
{
  global $pxpay;

  $enc_hex = $_REQUEST["result"];
  #getResponse method in PxPay object returns PxPayResponse object
  #which encapsulates all the response data
  $rsp = $pxpay->getResponse($enc_hex);

  if ($rsp->getStatusRequired() == "1")
  {
    $result = "An error has occurred.";
  }
  elseif ($rsp->getSuccess() == "1")
  {
    $result = "The transaction was approved.";
  }
  else
  {
    $result = "The transaction was declined.";
  }

  # the following are the fields available in the PxPayResponse object
  $Success           = $rsp->getSuccess();   # =1 when request succeeds
  $Retry             = $rsp->getRetry();     # =1 when a retry might help
  $StatusRequired    = $rsp->getStatusRequired();      # =1 when transaction "lost"
  $AmountSettlement  = $rsp->getAmountSettlement();
  $AuthCode          = $rsp->getAuthCode();  # from bank
  $CardName          = $rsp->getCardName();  # e.g. "Visa"
  $DpsTxnRef	     = $rsp->getDpsTxnRef();

  # the following values are returned, but are from the original request
  $TxnType           = $rsp->getTxnType();
  $TxnData1          = $rsp->getTxnData1();
  $TxnData2          = $rsp->getTxnData2();
  $TxnData3          = $rsp->getTxnData3();
  $CurrencyInput     = $rsp->getCurrencyInput();
  $EmailAddress      = $rsp->getEmailAddress();
  $MerchantReference = $rsp->getMerchantReference();
  $ResponseText = $rsp->getResponseText();

  # is there a nice way to print all the XML values returned?

  print <<<HTMLEOF
<html>
<head>
<title>Direct Payment Solutions: Secure Payments Page PHP Results</title>
</head>
<body>
<h1>Direct Payment Solutions: Secure Payments Page PHP Results</h1>
<p>$result</p>
<table border=1>
  <tr><th>Element</th>          <th>Value</th> </tr>
  <tr><td>Success</td>          <td>&nbsp;$Success</td></tr>
  <tr><td>Retry</td>            <td>&nbsp;$Retry</td></tr>
  <tr><td>StatusRequired</td>   <td>&nbsp;$StatusRequired</td></tr>
  <tr><td>AmountSettlement</td> <td>&nbsp;$AmountSettlement</td></tr>
  <tr><td>AuthCode</td>         <td>&nbsp;$AuthCode</td></tr>
  <tr><td>CardName</td>         <td>&nbsp;$CardName</td></tr>
  <tr><td>DpsTxnRef</td>         <td>&nbsp;$DpsTxnRef</td></tr>
  <tr><td>ResponseText</td>         <td>&nbsp;$ResponseText</td></tr>
</table>
</body>
</html>
HTMLEOF;
}

#******************************************************************************
# This function prints a blank purchase form.
#******************************************************************************
function print_form()
{
  print <<<HTMLEOF
<html>
<head>
<title>Direct Payment Solutions: Secure Payments Page PHP Sample</title>
</head>
<body>
<h1>Direct Payment Solutions: Secure Payments Page PHP Sample</h1>
<p>
You have indicated you would like to buy some widgets.
</p>
<p>
Please enter the number of widgets below, and enter your
shipping details.
</p>
<form method="post">
<table>
  <tr>
    <td>Quantity:</td>
    <td><input name="Quantity" type="text"/></td>
    <td>@ $1.20 ea</td>
  </tr>
  <tr>
    <td>Ship to</td>
    <td></td>
  </tr>
  <tr>
    <td>Address:</td>
    <td><input name="Address1" type="text"/></td>
  </tr>
  <tr>
    <td>City:</td>
    <td><input name="Address2" type="text"/></td>
  </tr>
</table>
<input name="Submit" type="submit" value="Submit"/>
&nbsp;Click submit to go to the secure payment page.
</form>
</body>
</html>
HTMLEOF;
}

#******************************************************************************
# This function formats data into a request and redirects to the
# Payments Page.
#******************************************************************************
function redirect_form()
{
  global $pxpay;

  $request = new PxPayRequest();

  $http_host   = getenv("HTTP_HOST");
  $request_uri = getenv("SCRIPT_NAME");
  $server_url  = "http://$http_host";
  #$script_url  = "$server_url/$request_uri"; //using this code before PHP version 4.3.4
  #$script_url  = "$server_url$request_uri"; //Using this code after PHP version 4.3.4
  $script_url = (version_compare(PHP_VERSION, "4.3.4", ">=")) ?"$server_url$request_uri" : "$server_url/$request_uri";


  # the following variables are read from the form
  $Quantity = $_REQUEST["Quantity"];
  $Address1 = $_REQUEST["Address1"];
  $Address2 = $_REQUEST["Address2"];
  $AmountInput = 1.20 * $Quantity;
  #Set up PxPayRequest Object
  $request->setAmountInput($AmountInput);
  $request->setTxnData1("Widget order");# whatever you want to appear
  $request->setTxnData2($Address1);		# whatever you want to appear
  $request->setTxnData3($Address2);		# whatever you want to appear
  $request->setTxnType("Purchase");
  $request->setInputCurrency("NZD");
  $request->setMerchantReference("123456"); # fill this with your order number
  $request->setEmailAddress("your_email@dps.co.nz");
  $request->setUrlFail($script_url);			# can be a dedicated failure page
  $request->setUrlSuccess($script_url);	# can be a dedicated success page


  #Call makeResponse of PxPay object to obtain the 3-DES encrypted payment request
  $request_string = $pxpay->makeRequest($request);

  $response = new MifMessage( $request_string );
  $url = $response->get_element_text("URI");
  $valid = $response->get_attribute("valid");
 # echo "request_string:".$request_string;
 #  exit;
   header("Location: ".$url);
}



?>
