<?php

require_once __DIR__.'/iniatialization.php';


/* Testing payments */


$description = "Test payment"; // a concise description with possible references to order name and such
$amount = 100.00;	 // as a float
$currency = "EUR"; // if set to null, will default to EUR as string
$debtorReference = "1234023"; 
$dueDateTime = null; // set it automatically a day in advance. if you want to set it, use a datetime string in "YYYY-MM-DD H:i:s" format

$returnUrl = "";  // set this if you want to override the return URL in your own callback function location specific for payments. If empty string or not given, the config defined return URL will be used.

$entranceCode = $bluem_object->CreateEntranceCode();

// To create AND perform a request:
$request = $bluem_object->CreatePaymentRequest(
	$description,
	$debtorReference,
	$amount,
	$dueDateTime,
	$currency,
	$entranceCode,
	$returnUrl
);

$response = $bluem_object->PerformRequest($request);

// The EntranceCode is set by the response; it is required for following status requests.
$entranceCode = $attrs['entranceCode'] . "";
// @todo save the entrance code in your local data store

if (isset($response->EMandateTransactionResponse->TransactionURL)) {
	$transactionURL = ($response->EMandateTransactionResponse->TransactionURL . "");
	// @todo redirect to the above transaction URL and save the initiated transaction 

} else { 
	// @todo no proper status given, show an error.
}
