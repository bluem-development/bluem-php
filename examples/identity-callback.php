<?php

require_once __DIR__ . '/initialization.php';



// if you use this same script for the callback, use a condition like this:

if (!isset($_GET['action']) || $_GET['action'] !== "callback") {
    // not properly initiated
} else {
    /** Callback example: */
    // parse the callback functionality here. THis is done in one file for simplicity's sake. It is recommended to do this in a separate file

    // retrieve from a store, preferably more persistent than session. 
    // this is purely for demonstrative purposes
    // if you are to use session, make sure it is sanitized
    $transactionID = $_SESSION['transactionID'];
    $entranceCode = $_SESSION['entranceCode'];


    $statusResponse = $bluem_object->IdentityStatus(
        $transactionID,
        $entranceCode
    );
    // var_dump($statusResponse);


    if ($statusResponse->ReceivedResponse()) {

        $statusCode = ($statusResponse->GetStatusCode());

        switch ($statusCode) {
            case 'Success':
                // do what you need to do in case of success!

                // retrieve a report that contains the information based on the request type:
                $identityReport = $statusResponse->GetIdentityReport();

                // this contains an object with key-value pairs of relevant data from the bank:
                /**
                 * example contents:
                 *  ["DateTime"]=>
                             string(24) "2020-10-16T15:30:45.803Z"
                            ["CustomerIDResponse"]=>
                            string(21) "FANTASYBANK1234567890"
                            ["AddressResponse"]=>
                            object(Bluem\BluemPHP\IdentityStatusBluemResponse)#4 (5) {
                                ["Street"]=>
                                string(12) "Pascalstreet"
                                ["HouseNumber"]=>
                                string(2) "19"
                                ["PostalCode"]=>
                                string(6) "0000AA"
                                ["City"]=>
                                string(6) "Aachen"
                                ["CountryCode"]=>
                                string(2) "DE"
                            }
                            ["BirthDateResponse"]=>
                            string(10) "1975-07-25"
                 */
                // store that information and process it. 

                // You can for example use the BirthDateResponse to determine the age of the user and act accordingly


                break;
            case 'Processing':
            case 'Pending':
                // do something when the request is still processing (for example tell the user to come back later to this page)
                break;
            case 'Cancelled':
                // do something when the request has been canceled by the user
                break;
            case 'Open':
                // do something when the request has not yet been completed by the user, redirecting to the transactionURL again 
                break;
            case 'Expired':
                // do something when the request has expired
                break;
            default:
                // unexpected status returned, show an error
                break;
        }
    } else {
        // no proper response received, tell the user
    }
}
