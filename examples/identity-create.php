<?php

require_once __DIR__ . '/initialization.php';



/*
	understanding types:
 */

// to retrieve a list of all possible identity request types, which can be useful for reference
$possible_types = $bluem_object->GetIdentityRequestTypes();
/*
returns: 
[
	"CustomerIDRequest",
	"NameRequest",
	"AddressRequest",
	"BirthDateRequest",
	"AgeCheckRequest",
	"GenderRequest",
	"TelephoneRequest",
	"EmailRequest"
	]
	*/

/* Creating an identity request, example */
$description = "Test identity"; // description is shown to customer
$debtorReference = "1234"; // client reference/number

$returnURL = "https://yourdomain.com/integration/identity.php?action=callback"; // provide a link here to the callback function; either in this script or another script

session_start();


// To create AND perform a request:
$request = $bluem_object->CreateIdentityRequest(
	["BirthDateRequest", "AddressRequest"],
	$description,
	$debtorReference,
	$returnURL
);

$response = $bluem_object->PerformRequest($request);

if ($response->ReceivedResponse()) {

	$entranceCode = $response->GetEntranceCode();
	$transactionID = $response->GetTransactionID();
	$transactionURL = $response->GetTransactionURL();
	// save this somewhere in your data store

	$_SESSION['entranceCode'] = $entranceCode;
	$_SESSION['transactionID'] = $transactionID;
	$_SESSION['transactionURL'] = $transactionURL;

	// direct the user to this place
	// header("Location: ".$transactionURL);

	// .. or for now, just show the URL:
	echo "TransactionURL: " . $transactionURL;
} else {
	// no proper response received, tell the user
}
