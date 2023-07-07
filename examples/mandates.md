```php
$bluem_object = new Bluem(...);

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

if ($response->ReceivedResponse()) {


// The EntranceCode is set by the response; it is required for following status requests.
    $entranceCode = $response->GetEntranceCode();
    // Suggestion: save this entrancecode somewhere in your local data store
    // ...

    $transactionURL = $response->GetTransactionURL();
    // Suggestion: redirect to the above transaction URL and save the initiated transaction

} else {
	// Suggestion: no proper status given, show an error.
	throw new Exception(
		"An error occured in the payment method.
		Please contact the webshop owner with this message:  " .
		$response->error()
	);
}

// --------------------------------------------
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

if ($statusCode === "Success") {

	// Implement: do what you need to do to mark the transaction completed on your end

} elseif ($statusCode === "Cancelled") {

	// Implement: do what you need to when the transaction has been cancelled

} elseif ($statusCode === "Open" || $statusCode === "Pending") {

	// the transaction is still open. it might take some time to process
	// Implement: show a message that reflects this

} elseif ($statusCode === "Expired") {

	// Implement: show a message that reflects an expired transaction;

} else {

	// unknown status occurred

//	echo "Error: Unknown or incorrect status retrieved: {$statusCode}
//		<br>Contact the administrator and communicate this status";
//    exit;
}
```
