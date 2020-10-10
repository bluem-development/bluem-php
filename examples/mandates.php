<?php 

require_once __DIR__.'/iniatialization.php';

/* Testing mandates */
/** 
 * Creating a Mandate 
 * */


$order_id = "1234";
$customer_id = "5678";

$mandate_id = $this->bluem->CreateMandateId(
	$order_id, 
	$customer_id
);

$response = $this->bluem->Mandate(
	$customer_id,
	$order_id,
	$mandate_id
);

if (is_a($response, "Bluem\BluemPHP\ErrorBluemResponse", false)) 
{
	throw new Exception(
		"An error occured in the payment method. 
		Please contact the webshop owner with this message:  " . 
		$response->error()
	);
}

$attrs = $response->EMandateTransactionResponse->attributes();

if (!isset($attrs['entranceCode'])) {
	throw new Exception(
		"An error occured in reading the transaction response. 
		Please contact the webshop owner"
	);
}

// The EntranceCode is set by the response; it is required for following status requests.
$entranceCode = $attrs['entranceCode'] . "";
// @todo save this entrancecode somewhere in your local data store


if (isset($response->EMandateTransactionResponse->TransactionURL)) {
	$transactionURL = ($response->EMandateTransactionResponse->TransactionURL . "");
	// @todo redirect to the above transaction URL and save the initiated transaction 

} else { 
	// @todo no proper status given, show an error.
}


/** 
 * Requesting a Mandate status
 * */

// retrieve these parameters from your local data store
$mandateID = "12345";
$entranceCode = "20200921162354249";

$response = $bluem_object->MandateStatus($mandateID, $entranceCode);


if (!$response->Status()) {
	echo ("Error when retrieving status: " . $response->Error() . "<br>Please contact the webshop and mention this status");
	exit;
}

$statusUpdateObject = $response->EMandateStatusUpdate;
$statusCode = $statusUpdateObject->EMandateStatus->Status . "";
// var_dump($statusCode);
if ($statusCode === "Success") {

	// @todo do what you need to do to mark the transaction completed on your end
	
} elseif ($statusCode === "Cancelled") {
	
	// @todo do what you need to when the transaction has been cancelled

} elseif ($statusCode === "Open" || $statusCode == "Pending") {
	
	// the transaction is still open. it might take some time to process
	// @todo show a message that reflects this
	
} elseif ($statusCode === "Expired") {

	// @todo show a message that reflects an expired transaction;

} else {

	// unknown status occurred

	echo "Error: Unknown or incorrect status retrieved: {$statusCode}
		<br>Contact the administrator and communicate this status";
}

