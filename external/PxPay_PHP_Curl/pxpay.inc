<?php

#******************************************************************************
#* Name          : PxPay.inc
#* Description   : The objects for PX Payment page  
#* Copyright	 : Direct Payment Solutions 2007(c)
#* Date          : 2006-03-24
#* Modifications : 
#* Version	 : 1.0
#******************************************************************************

# MifMessage.
# Use this class to parse a DPS PX MifMessage in XML form,
# and access the content.
class MifMessage
{
  var $xml_;
  var $xml_index_;
  var $xml_value_;

  # Constructor:
  # Create a MifMessage with the specified XML text.
  # The constructor returns a null object if there is a parsing error.
  function MifMessage($xml)
  {
    $p = xml_parser_create();
    xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
    $ok = xml_parse_into_struct($p, $xml, $value, $index);
    xml_parser_free($p);
    if ($ok)
    {
      $this->xml_ = $xml;
      $this->xml_value_ = $value;
      $this->xml_index_ = $index;
    }
    #print_r($this->xml_value_); # JH_DEBUG
  }

  # Return the value of the specified top-level attribute.
  # This method can only return attributes of the root element.
  # If the attribute is not found, return "".
  function get_attribute($attribute)
  {
    #$attribute = strtoupper($attribute);
    $attributes = $this->xml_value_[0]["attributes"];
    return $attributes[$attribute];
  }

  # Return the text of the specified element.
  # The element is given as a simplified XPath-like name.
  # For example, "Link/ServerOk" refers to the ServerOk element
  # nested in the Link element (nested in the root element).
  # If the element is not found, return "".
  function get_element_text($element)
  {
    #print_r($this->xml_value_); # JH_DEBUG
    $index = $this->get_element_index($element, 0);
    if ($index == 0)
    {
      return "";
    }
    else
    {
	## TW2004-09-24: Fixed bug when elemnt existent but empty
    #
    $elementObj = $this->xml_value_[$index];
    if (! array_key_exists("value", $elementObj))
      return "";
   
    return $this->xml_value_[$index]["value"];
    }
  }

  # (internal method)
  # Return the index of the specified element,
  # relative to some given root element index.
  #
  function get_element_index($element, $rootindex = 0)
  {
    #$element = strtoupper($element);
    $pos = strpos($element, "/");
    if ($pos !== false)
    {
      # element contains '/': find first part
      $start_path = substr($element,0,$pos);
      $remain_path = substr($element,$pos+1);
      $index = $this->get_element_index($start_path, $rootindex);
      if ($index == 0)
      {
        # couldn't find first part; give up.
        return 0;
      }
      # recursively find rest
      return $this->get_element_index($remain_path, $index);
    }
    else
    {
      # search from the parent across all its children
      # i.e. until we get the parent's close tag.
      $level = $this->xml_value_[$rootindex]["level"];
      if ($this->xml_value_[$rootindex]["type"] == "complete")
      {
        return 0;   # no children
      }
      $index = $rootindex+1;
      while ($index<count($this->xml_value_) && 
             !($this->xml_value_[$index]["level"]==$level && 
               $this->xml_value_[$index]["type"]=="close"))
      {
        # if one below parent and tag matches, bingo
        if ($this->xml_value_[$index]["level"] == $level+1 &&
#            $this->xml_value_[$index]["type"] == "complete" &&
            $this->xml_value_[$index]["tag"] == $element)
        {
          return $index;
        }
        $index++;
      }
      return 0;
    }
  }
}

class PxPay_Curl
{
	var $PxPay_Key;
	var $PxPay_Url;
	var $PxPay_Userid;
	function PxPay_Curl($Url, $UserId, $Key){
		error_reporting(E_ERROR);
		$this->PxPay_Key = $Key;
		$this->PxPay_Url = $Url;
		$this->PxPay_Userid = $UserId;
	}
	
	#******************************************************************************
	# Create an encoded request for the PxPay Host.
	#******************************************************************************
	function makeRequest($request)
	{
		#Validate the Request
		if($request->validData() == false) return "" ;
			
  		$txnId = uniqid("MI");  #You need to generate you own unqiue reference. 
		$request->setTxnId($txnId);
		$request->setTs($this->getCurrentTS());
		$request->setSwVersion("1.0");
		$request->setAppletType("PHPPxPay");		
		$request->setUserId($this->PxPay_Userid);
		$request->setKey($this->PxPay_Key);
		
		$xml = $request->toXml();
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->PxPay_Url);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		#set up proxy, this may change depending on ISP, please contact your ISP to get the correct curl settings
		#curl_setopt($ch,CURLOPT_PROXY , "proxy:8080");
		#curl_setopt($ch,CURLOPT_PROXYUSERPWD,"username:password");
		#curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
		$result = curl_exec ($ch); 		
			
		curl_close ($ch);
  
		return $result;
		
	}
			
	#******************************************************************************
	# Return the decoded response from the PxPay Host.
	#******************************************************************************
	function getResponse($resp_enc){
				
		$xml = "<ProcessResponse><PxPayUserId>".$this->PxPay_Userid."</PxPayUserId><PxPayKey>".$this->PxPay_Key."</PxPayKey><Response>".$resp_enc."</Response></ProcessResponse>";
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->PxPay_Url);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		#set up proxy, this may change depending on ISP, please contact your ISP to get the correct curl settings
		#curl_setopt($ch,CURLOPT_PROXY , "proxy:8080");
		#curl_setopt($ch,CURLOPT_PROXYUSERPWD,"username:password");
		#curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
		$result = curl_exec ($ch); 		
			
		curl_close ($ch);
		
		$pxresp = new PxPayResponse($result);
		return $pxresp;	
	}
	
	#******************************************************************************
	# Return the current time (GMT/UTC).The return time formatted YYYYMMDDHHMMSS.
	#******************************************************************************
	function getCurrentTS()
	{
	  
	  return gmstrftime("%Y%m%d%H%M%S", time());
	}
	

}

#******************************************************************************
# Class for PxPay request messages.
#******************************************************************************
class PxPayRequest extends PxPayMessage
{
	var $TxnId,$UrlFail,$UrlSuccess;
	var $AmountInput, $AppletVersion, $CurrencyInput;
	var $EnableAddBillCard;
	var $TS;
	var $PxPayUserId;
	var $PxPayKey;
	
	var $AppletType;
	
	#Constructor
 	function PxPayRequest(){
		$this->PxPayMessage();
		
	}
	
	function setAppletType($AppletType){
		$this->AppletType = $AppletType;
	}
	
	function getAppletType(){
		return $this->AppletType;
	}
	
	
	
	function setTs($Ts){
		$this->TS = $Ts;
	}
	function setEnableAddBillCard($EnableBillAddCard){
	 $this->EnableAddBillCard = $EnableBillAddCard;
	}
	
	function getEnableAddBillCard(){
		return $this->EnableAddBillCard;
	}
	function setInputCurrency($InputCurrency){
		$this->CurrencyInput = $InputCurrency;
	}
	function getInputCurrency(){
		return $this->CurrencyInput;
	}
	function setTxnId( $TxnId)
	{
		$this->TxnId = $TxnId;
	}
	function getTxnId(){
		return $this->TxnId;
	}
	
	function setUrlFail($UrlFail){
		$this->UrlFail = $UrlFail;
	}
	function getUrlFail(){
		return $this->UrlFail;
	}
	function setUrlSuccess($UrlSuccess){
		$this->UrlSuccess = $UrlSuccess;
	}
	function setAmountInput($AmountInput){
		$this->AmountInput = sprintf("%9.2f",$AmountInput); 
	}
	
	function getAmountInput(){
			
		return $this->AmountInput;
	}
	function setUserId($UserId){
		$this->PxPayUserId = $UserId;
	}
	
	function setKey($Key){
		$this->PxPayKey = $Key;
	}
	
	function setSwVersion($SwVersion){
		$this->AppletVersion = $SwVersion;
	}
	
	function getSwVersion(){
		return $this->AppletVersion;
	}
	#******************************************************************
	#Data validation 
	#******************************************************************
	function validData(){
		$msg = "";
		if($this->TxnType != "Purchase")
			if($this->TxnType != "Auth")
				if($this->TxnType != "GetCurrRate")
					if($this->TxnType != "Refund")
						if($this->TxnType != "Complete")
							if($this->TxnType != "Order1")
								$msg = "Invalid TxnType[$this->TxnType]<br>";
		
		if(strlen($this->MerchantReference) > 64)
			$msg = "Invalid MerchantReference [$this->MerchantReference]<br>";
		
		if(strlen($this->TxnId) > 16)
			$msg = "Invalid TxnId [$this->TxnId]<br>";
		if(strlen($this->TxnData1) > 255)
			$msg = "Invalid TxnData1 [$this->TxnData1]<br>";
		if(strlen($this->TxnData2) > 255)
			$msg = "Invalid TxnData2 [$this->TxnData2]<br>";
		if(strlen($this->TxnData3) > 255)
			$msg = "Invalid TxnData3 [$this->TxnData3]<br>";
			
		if(strlen($this->EmailAddress) > 255)
			$msg = "Invalid EmailAddress [$this->EmailAddress]<br>";
			
		if(strlen($this->UrlFail) > 255)
			$msg = "Invalid UrlFail [$this->UrlFail]<br>";
		if(strlen($this->UrlSuccess) > 255)
			$msg = "Invalid UrlSuccess [$this->UrlSuccess]<br>";
		if(strlen($this->BillingId) > 32)
			$msg = "Invalid BillingId [$this->BillingId]<br>";
		if(strlen($this->DpsBillingId) > 16)
			$msg = "Invalid DpsBillingId [$this->DpsBillingId]<br>";
			
		if ($msg != "") {
		    trigger_error($msg,E_USER_ERROR);
			return false;
		}
		return true;
	}

}

#******************************************************************************
# Abstract base class for PxPay messages.
# These are messages with certain defined elements,  which can be serialized to XML.

#******************************************************************************
class PxPayMessage {
	var $TxnType;
  	var $TxnData1;
  	var $TxnData2;
  	var $TxnData3;
  	var $MerchantReference;
  	var $EmailAddress;
  	var $BillingId;
  	var $DpsBillingId;
	var $DpsTxnRef;
	
	function PxPayMessage(){
	
	}
	function setDpsTxnRef($DpsTxnRef){
		$this->DpsTxnRef = $DpsTxnRef;
	}
	
	function getDpsTxnRef(){
		return $this->DpsTxnRef;
	}
	
	function setDpsBillingId($DpsBillingId){
		$this->DpsBillingId = $DpsBillingId;
	}
	
	function getDpsBillingId(){
		return $this->DpsBillingId;
	}
	function setBillingId($BillingId){
		$this->BillingId = $BillingId;
	}
	
	function getBillingId(){
		return $this->BillingId;
	}
	function setTxnType($TxnType){
		$this->TxnType = $TxnType;
	}
	function getTxnType(){
		return $this->TxnType;
	}
	function setMerchantReference($MerchantReference){
		$this->MerchantReference = $MerchantReference;
	}
	
	function getMerchantReference(){
		return $this->MerchantReference;
	}
	function setEmailAddress($EmailAddress){
		$this->EmailAddress = $EmailAddress;
		
	}
	
	function getEmailAddress(){
		return $this->EmailAddress;
	}
	
	function setTxnData1($TxnData1){
		$this->TxnData1 = $TxnData1;
		
	}
	function getTxnData1(){
		return $this->TxnData1;
	}
	function setTxnData2($TxnData2){
		$this->TxnData2 = $TxnData2;
		
	}
	function getTxnData2(){
		return $this->TxnData2;
	}
	
	function getTxnData3(){
		return $this->TxnData3;
	}
	function setTxnData3($TxnData3){
		$this->TxnData3 = $TxnData3;
		
	}
	function toXml(){
		$arr = get_object_vars($this);
		$root = strtolower(get_class($this));
#echo "<br>root:".$root;
		if($root == "pxpayrequest")
			$root = "GenerateRequest";
		elseif ($root == "pxpayresponse")
			$root = "Response";
		else
			$root ="Request";
			
		$xml  = "<$root>";
    	while (list($prop, $val) = each($arr))
        	$xml .= "<$prop>$val</$prop>" ;

		$xml .= "</$root>";
		return $xml;
	}
	
	
}

#******************************************************************************
# Class for PxPay response messages.
#******************************************************************************

class PxPayResponse extends PxPayMessage
{
	var $Success;
	var $StatusRequired;
	var $Retry;
	var $AuthCode;
	var $AmountSettlement;
	var $CurrencySettlement;
	var $CardName;
	var $CardHolderName;
	var $CurrencyInput;
	var $UserId;
	var $ResponseText;
	#var $DpsTxnRef;
	var $MerchantTxnId;
	var $TS;
  
	function PxPayResponse($xml){
		$msg = new MifMessage($xml);
		$this->PxPayMessage();
		
	
		$this->setBillingId($msg->get_element_text("BillingId"));
		$this->setDpsBillingId($msg->get_element_text("DpsBillingId"));
		$this->setEmailAddress($msg->get_element_text("EmailAddress"));
		$this->setMerchantReference($msg->get_element_text("MerchantReference"));
		$this->setTxnData1($msg->get_element_text("TxnData1"));
		$this->setTxnData2($msg->get_element_text("TxnData2"));
		$this->setTxnData3($msg->get_element_text("TxnData3"));
		$this->setTxnType($msg->get_element_text("TxnType"));		
		$this->Success = $msg->get_element_text("Success");
		$this->AuthCode = $msg->get_element_text("AuthCode");
		$this->AmountSettlement = $msg->get_element_text("AmountSettlement");
		$this->CurrencySettlement = $msg->get_element_text("CurrencySettlement");
		$this->CardName = $msg->get_element_text("CardName");
		$this->ResponseText = $msg->get_element_text("ResponseText");
		$this->DpsTxnRef = $msg->get_element_text("DpsTxnRef");
		$this->MerchantTxnId = $msg->get_element_text("TxnId");
		$this->CardHolderName = $msg->get_element_text("CardHolderName");
		$this->CurrencyInput = $msg->get_element_text("CurrencyInput");
		
	}
	function getTS(){
		return $this->TS;
	}
	function getMerchantTxnId(){
		return $this->MerchantTxnId;
	}
	
	function getResponseText(){
		return $this->ResponseText;
	}
	function getUserId(){
		return $this->UserId;
	}
	function getCurrencyInput(){
		return $this->CurrencyInput;
	}
	function getCardName(){
		return $this->CardName;
	}
	function getCurrencySettlement(){
		$this->CurrencySettlement;
	}
	function getAmountSettlement(){
		return $this->AmountSettlement;
	}
	function getSuccess(){
		return $this->Success;
	}
	function getStatusRequired(){
		return $this->StatusRequired;
	}
	function getRetry(){
		return $this->Retry;
	}
	function getAuthCode(){
		return $this->AuthCode;
	}
	#******************************************************************************
	# Return the expired time, i.e. 2 days ago (GMT/UTC).
	#JZ2004-08-30
	#******************************************************************************
	function  getExpiredTS()
	{
	  
	  return gmstrftime("%Y%m%d%H%M%S", time()- 2 * 24 * 60 * 60);
	}
	
}

?>