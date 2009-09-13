#******************************************************************************
#* Name          : README_PxPay_PHP_CURL
#* Description   : Secure Payments Page PHP Sample - PxPay
#* Copyright	 : Direct Payment Solutions 2007(c)
#* Date          : 2007-01-04 
#* References    : http://www.paymentexpress.com/blue.asp?id=d_pxpay
#* Modifications : 
#* Version	 : 1.0
#******************************************************************************

NOTE!!
======
This sample code is a reference how to interface the PxPay Xml Interface.
No Security issues for PHP were addressed and should be treated on a seperate note on the client side. This is the responsibility of the client to address these issues.

Development Spec
================
Windows Xp with Service pack 2
PHP Version 5.0.2
Microsoft-IIS/5.1

Different specifications might cause problems during development and should be address.

OVERVIEW
========

This PHP script is a sample, intended to show how a merchant can use
the DPS Secure Payments Page from PHP.

The Secure Payments Page is for sites that do not wish to own a digital certificate for their site or hold the secure payment page on their host, which reduces the cost to the merchant.

These sites can obtain order details via ordinary form requests, and
then obtain payment authorization via the DPS Secure Payments Page.

There are two files included in the release:
	1. pxpay.inc 
	   -- PHP include file which contains classes for Payments Page
	2. PxPay_Sample_Curl.php
	   -- For clients using Curl on their host
	   -- Sample code to post payment and process response using PxPay,
	      PxPayReQuest, PxPayResponse objects
	

There are two basic steps in using PxPay:

  1- Sending the transaction request to the Secure Payments Page.
  2- Handling the response that is sent back.

Sending the transaction request
===============================

To generate a request, follow these steps:
  1- Insert your PxPay_Key, PxPay_Userid and PxPay_URl into the PxPay sample code.
  2- Set up an PxpayRequest object by giving transaction details.
  3- use PXPay.makeRequest function to create an ASCII hex code encrypted MifMessage
  4- create a MifMessage from the response and extract the URL
  5- Redirect the client


These steps are shown in the redirect_form method in the sample.

Handling the response
=====================

  1- Get the ASCII hex representation of the result.
  2- use PxPay.getResponse method to get the PxPayResponse object with unencrypt response XML fields data.
  3- Create a result page for the client using pxPayResponse object.

These steps are shown in the print_result method in the sample.

PREREQUISITES
=============

   -  Curl 

INSTALLATION
============

   1. Install PxPay_Sample_Curl.php into wherever you put your PHP scripts.
   2. Install pxpay.inc into your PHP include path, which is set up in php.ini
   3. Set your username and Encryption key in the PxPay_Sample_Curl.php file into the variables 
      $PxPay_Userid and $PxPay_Key