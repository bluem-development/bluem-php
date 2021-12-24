<?php
/**
 * Bluem-PHP examples: Identity - Request creation
 * This file contains examples and annotations for using the `bluem-php` package.
 * All to-dos are for your reference where action on your part is still required.
 *
 * Code is courtesy of and property of Bluem Payment Services
 * Author: Daan Rijpkema (d.rijpkema@bluem.nl)
 */
require_once __DIR__ . '/initialization.php';

/**
 * understanding types:
 * to retrieve a list of all possible identity request types, which can be useful for reference
 */
$possible_types = $bluem_object->GetIdentityRequestTypes();
/*
returns:
[
	"CustomerIDRequest",
	"NameRequest",
	"AddressRequest",
	"BirthDateRequest",
	"GenderRequest",
	"TelephoneRequest",
	"EmailRequest"
	"AgeCheckRequest",
    "CustomerIDLoginRequest",
]
	Use either AgeCheckRequest OR the others in a request.
*/

/* Creating an identity request, example */
/* Description to be shown to the customer */
$description = "Test identity"; 

/* Debtor Reference: client or customer reference/number. This is an optional value
    and can also be left empty. */
$debtorReference = "1234"; 

/* provide a link here to the callback function; either in this script or another script */
$returnURL = "https://yourdomain.com/integration/identity.php?action=callback"; 

// create a session. 
if(session_status() !== PHP_SESSION_ACTIVE) 
    session_start();

// To create AND perform a request:
$request = $bluem_object->CreateIdentityRequest(
    ["BirthDateRequest", "AddressRequest"],
    $description,
    $debtorReference
);

$response = $bluem_object->PerformRequest($request);

if ($response->ReceivedResponse()) {

	$entranceCode = $response->GetEntranceCode();
	$transactionID = $response->GetTransactionID();
	$transactionURL = $response->GetTransactionURL();
	
    // Save this information in your data store
	$_SESSION['entranceCode'] = $entranceCode;
	$_SESSION['transactionID'] = $transactionID;
	$_SESSION['transactionURL'] = $transactionURL;
    
    // Direct the user to this place
	 header("Location: ".$transactionURL);

} else {
	// no proper response received, tell the user
    
}
