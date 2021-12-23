<?php
/**
 * Bluem-PHP examples: Payments
 * This file contains examples and annotations for using the `bluem-php` package.
 * All to-dos are for your reference where action on your part is still required.
 *
 * Code is courtesy of and property of Bluem Payment Services
 * Author: Daan Rijpkema (d.rijpkema@bluem.nl)
 */
require_once __DIR__.'/initialization.php';

/*
 * Creating a payment
 */
// description: a concise description with possible references to order name and such
$description = "Test payment"; 
// amount: as a float
$amount = 100.00;	 
// currency: if set to null, will default to EUR as string
$currency = "EUR"; 
// any additional reference you want to make to the customer, user or order
$debtorReference = "1234023"; 
// dueDateTime: set it automatically a day in advance. if you want to set it, use a datetime string in "YYYY-MM-DD H:i:s" format
$dueDateTime = null; 

// returnUrl: set this if you want to override the return URL in your own callback function location specific for payments. If empty string or not given, the config defined return URL will be used.
$returnUrl = "";  

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


if ($response->ReceivedResponse()) {

    $transactionURL = $response->GetTransactionURL();
    
    // The EntranceCode is set by the response; it is required for following status requests.
    $entranceCode = $response->GetEntranceCode();
    
    // Suggestion: save the initiated transaction details; for example
    $_SESSION['entranceCode'] = $entranceCode;
    
    // Suggestion: redirect to the above transaction URL  
    header("Location: ".$transactionURL);
} else { 
    $error = $response->Error();
	// To implement yourself: no proper status given, show an error.
    echo "Error: ".$error;
    exit;
}





// --------------------------------------------
/**
 * Requesting a Payment status
 * */

// retrieve these parameters from your local data store
$transactionID = "12345";
$entranceCode = "20200921162354249";

$response = $bluem_object->PaymentStatus($transactionID, $entranceCode);


if (!$response->Status()) {
    echo "Error when retrieving status: " . $response->Error() . 
        "<br>Please contact the webshop and mention this status";
    exit;
}

$statusUpdateObject = $response->EMandateStatusUpdate;
$statusCode = $statusUpdateObject->EMandateStatus->Status . "";

if ($statusCode === "Success") {
    
    // deal with a proper payment
} elseif ($statusCode === "Cancelled") {

    // Implement: do what you need to when the transaction has been cancelled

} elseif ($statusCode === "Open" || $statusCode == "Pending") {

    // the transaction is still open. it might take some time to process
    // Implement: show a message that reflects this

} elseif ($statusCode === "Expired") {

    // Implement: show a message that reflects an expired transaction;

} else {

    // unknown status occurred

    echo "Error: Unknown or incorrect status retrieved: $statusCode
		<br>Contact the administrator and communicate this status";
    exit;

}