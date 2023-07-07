Creating payments

```php
$bluem_object = new Bluem(...);

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
//    echo "Error: ".$error;
//    exit;
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
//    echo "Error when retrieving status: " . $response->Error() .
//        "<br>Please contact the webshop and mention this status";
//    exit;
}

$statusUpdateObject = $response->PaymentStatusUpdate;
$statusCode = $statusUpdateObject->PaymentStatus->Status . "";

if ($statusCode === "Success") {

    // deal with a proper payment
} elseif ($statusCode === "Cancelled") {

    // Implement: do what you need to when the transaction has been cancelled

} elseif ($statusCode === "Open" || $statusCode === "Pending") {

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

```

Creating a webhook for payments
```php
$bluem_object = new Bluem(...);

/*
 * Creating a webhook
 *
 * when running this in a webserver, this allows you to expose the webhook to the url like this:
 *
 * http://example.com/payments-webhook.php?action=webhook
 *
 * change this URL to match your web server.
 */

// This GET parameter is an example. This is not required.
if ($_GET['action'] === "webhook") {

    // if you want debug information and verbose results when testing the webhook, set this to true
    $bluem_object->setConfig("webhookDebug", false);

    // this call will exit with a 200 or 400 HTTP status code, and parse the incoming data
    // Returns null if the webhook didn't parse successfully.
    $webhook = $bluem_object->Webhook();

    // implement this like you implemented the callback for the regular services in your application
    if ($webhook !== null) {

        $status = $webhook->getStatus();

        if ($status === "Success") {

            $transactionID = $webhook->getTransactionID();
            $amount = $webhook->getAmount();
            $amountPaid = $webhook->getAmountPaid();
            $currency = $webhook->getCurrency();
            $paymentMethod = $webhook->getPaymentMethod();

            // note: these are currently iDEAL specific
            $debtorAccountName = $webhook->getDebtorAccountName();
            $debtorIBAN = $webhook->getDebtorIBAN();
            $debtorBankID = $webhook->getDebtorBankID();

            // deal with the successful callback

        } elseif($status ==="Cancelled") {
            // deal with the cancelled callback
        } elseif($status ==="Open") {
            // deal with the open callback
        } elseif($status ==="Expired"){
            // deal with the failed or expired callback
        } else {
            // deal with any other status
        }

        // refer to the readme on webhooks for example methods to use for retrieving data from the callback object.
    }
}
```
