# bluem-php

A PHP interface for utilizing the Bluem services such as eMandate, ePayments, iDIN and more.

Utilized by a range of other applications such as WordPress and WordPress WooCommerce plugins 
% add links %

Use this to write your own applications in PHP that communicate with Bluem.

**TIP:** refer to the `examples` folder within this repository for a full example implementation that you can base your own integration on.

## Installation

Run Composer to install this library and dependencies:

```bash
composer require daanrijpkema/bluem-php
```

## Configuration

Include the required autoload functions of Composer in your code. 

```php

// get composer dependencies
require_once __DIR__. '/vendor/autoload.php';


use Bluem\BluemPHP\Integration;

```

Then you can retrieve an object to utilize all functions as below. It is suggested to save these configuration settings in a database or settings handler, so they can be stored by the user instead of in code.

```php
$bluem_config = new Stdclass();

// Fill in prod, test or acc for production, test or acceptance environment.
$bluem_config->environment = ... 

// The sender ID, issued by BlueM. Starts with an S, followed by a number.
$bluem_config->senderID = ... 

// The access token to communicate with BlueM, for the test environment.
$bluem_config->test_accessToken = ... 

// The access token to communicate with BlueM, for the production environment.
$bluem_config->production_accessToken = ... 

// the merchant ID, to be found on the contract you have with the bank for receiving direct debit mandates.
$bluem_config->merchantID = ... 

// The slug of the thank you page to which should be referred after completing process. If your ORDERID is processed in the URL it will be filled in for you
$bluem_config->thanksPage = ... 

// What status would you like to get back for a TEST transaction or status request? Possible values: none, success, cancelled, expired, failure, open, pending
$bluem_config->expectedReturnStatus = ... 

// What's your BrandID? Set at BlueM
$bluem_config->brandID = ... 

// Brief description of the debt collection at the time of issue
$bluem_config->eMandateReason = ... 

// Choose type of collection: CORE or B2B
$bluem_config->localInstrumentCode = ... 

// URL to return to after finishing the process
$bluem_config->merchantReturnURLBase = ...;

$bluem_object = new Integration($bluem_config);
```

## General concept

Flow for every of the included services (eMandates, Payments, Identity and Iban Check) is very similar and thus easy to understand once you understand and implement one of them.

### TransactionRequest (from site to Bluem)
Your application creates a request object, sends it to Bluem's servers with authentication.
### TransactionResponse (including TransactionURL) (from Bluem to site)
Your application receives a response with a URL as entry point into the Bluem environment, if the request was successful. 

### Bluem Environment
Redirect client to TransactionURL, client (via checkout) to bank, confirm transaction, back to Merchant (via MerchantReturnURL with Emandates, and via DebtorReturnURL with Payments/Identity), this is trigger for StatusRequest
The user is redirected to the response URL at the Bluem servers. In this Bluem environment, the user performs a mandate signing, payment, identity or iban check and returns to a predefined URL. 

### StatusRequest (from site to Bluem)
Using this same package one can check the Status of a request using a second endpoint (given an ID and entranceCode of the transaction defined at the creation of the request). This is vital, as it allows you to change the status of an order or check within your site or app based on Bluem's status. It is recommended to do this check when the user comes back to your site/app directly after handling the transaction at Bluem AND using a webhook functionality. 

### StatusUpdate (from Bluem to site)
This response object that comes back from a callback or webhook, contains an updated status that you can process within your application. I.e. when the user has paid or verified, and you have to change a product, order or process' status and go to a next step.

### Webhook
The webhook functionality allows Bluem to directly push status changes and transaction results to your site or app in a trustworthy way. Therefore, your orders and transactions will always get updated to the corresponding statuses, no matter what your user does after visiting Bluem's transaction page.

Explanation that webhook is only needed for Mandate and Payment, online stores/portals that need to know directly the status, for those cases that client closes the browser at a bank after successful confirmation of transaction. Webhook not needed for iDIN as with iDIN the client ALWAYS comes back to website after successful identification (there is no place where the end user can close the browser).

**Using the webhook functionality is highly recommended.** More instructions on implementing the webhook will follow in each specific service shortly.


### DebtorWallet: preselecting a bank for Mandate, Payment or Identity request

It is possible to preselect a Bank by IssuerID (BIC) when creating a Mandate, Payment or Identity request. This can be used if you want to user to select the given bank in your own interface and skip the bank selection within the Bluem portal interface.

To preselect a bank in a request, use the following function on a request object (supported for Mandates, Payments and Identity only) for an example BIC:

```php
$BIC = "INGBNL2A";
$request->selectDebtorWallet($BIC);
```
Parameter has to be a valid BIC code of a supported bank. An invalid BIC code will trigger an exception. **Please note that supported BICs differ per service as not every bank offers the same services!** The supported BIC codes per service can also be requested from a Bluem object, given the service context. Below is a list of supported contexts for this function.

```php
$MandatesBICs = $bluem_object->retrieveBICsForContext("Mandates"); // also specific to localInstrumentCode, see notes.
$PaymentsBICs = $bluem_object->retrieveBICsForContext("Payments");
$IdentityBICs = $bluem_object->retrieveBICsForContext("Identity");
```
Input of a different context will trigger an exception. If valid, the result is an array of `Bluem\BluemPHP\BIC` objects with attributes `IssuerID` and `IssuerName`: the BIC and Bank name respectively. You can use this to populate your user inteface.

Please note that the BIC list will vary when a different `localInstrumentCode` is configured. The localInstrumentCode `CORE` and `B2B` are supported by different banks. Based on your configuration, the right BIC list is loaded from context automatically and used to verify the debtorWallet.


## eMandates 


### Mandate specific configuration fields:
The following attributes in the bluem_config are vital for proper eMandate functionality:
Wat is localInstrumentCode (CORE, B2B), 

requestType (Issuing, (Amendment/Cancellation worden eigenlijk niet gebruikt)), 

sequenceType (OOFF, RCUR), 

MandateID, 

merchantID, 

MaxAmount (for B2B), 

ValidationReference



### Creating an eMandate Transaction: helper functions
You need certain information to reference a transaction request: an ID (in this case the MandateID) and an entranceCode (basically a timestamp when you started the request). Creating this information can be done using helper functions. When creating a new transaction,  the entranceCode and MandateID will be generated within the `$bluem_object`.


Generating a mandate ID:
```php
$mandateId = $bluem_object->CreateMandateId($order_id, $customer_id);
```

Generating an entrance code:

```php
$entranceCode = $bluem_object->CreateEntranceCode();
```

#### Creating an eMandate transaction
The default transaction returns to a callback function at a specific URL that then automatically performs a Status Update and can perform further functionalities.
It uses the `merchantReturnURLBase` attribute, set in the parameter when creating the `$bluem_object` object to know where to redirect to expect this function.
This process automatically adds the mandateID as a GET parameter to the return URL, so it can be picked up for the Status Update.

```php
// default
$request = $bluem_object->CreateMandateRequest($customer_id,$order_id,"default");

// After creating any request, you will still have to perform the request:

$response = $bluem_object->PerformRequest($request);
```

Tip: you can also combine the creation and performance if the request in one function call, if you do not want to manipulate or read the request object beforehand:
```php
$response = $bluem_object->Mandate($customer_id, $order_id,"default");

```

If you do anything wrong or you are unauthorized, the Response object will be of type `Bluem\BluemPHP\ErrorBluemResponse` and has an `Error()` function to retrieve a string of information regarding your error that you could display to your user or handle yourself.

An example about incorrect access token and Ids could be: "Unauthorized: check your access credentials".

#### Redirection after eMandate transaction creation
When you have performed a transaction request successfully, you receive a response object from Bluem. This object tells you where to redirect the user to to actually perform the administrative steps at the Bluem portal.
```php
if ($response->ReceivedResponse()) {

	$entranceCode = $response->GetEntranceCode();
    // save the entranceCode in your data store

	$transactionURL = $response->GetTransactionURL();

    // TODO: redirect to the above transaction URL
    header("Location: ". $transactionURL); // or something of thes ort.
} else { 
    // TODO: no proper status given, show an error.
    exit("Error: " . $response->Error()); // for example
}
```

### Requesting an eMandate Transaction status

```php
$response = $bluem_object->MandateStatus(
    $existing_mandate_id,
    $existing_entrance_code
);
if (!$response->Status()) {
    // no valid response received
} else {
    if ($response->EMandateStatusUpdate->EMandateStatus->Status . "" === "Success") { // casting to string
        // successful status response
    } else {
        // different status response
    }
}
```

The possible statuses are `Success`, `Processing`, `Pending`, `Cancelled`, `Open` and `Expired`. Refer to the Bluem documentation for more specifics on these statuses.

## Payments

The following attributes in the bluem_config are vital for proper eMandate functionality:

What is PaymentReference, 

Amount (Amount Mutable, MinAmount, MaxAmount, AmountArray),


### Create a payment transaction 

Payments is very similar to eMandates, but utilizing other parameters:

```php
    $description = "Test payment";
    $amount = 100.00; // has to be a float
    $currency = "EUR"; // if set to null, will default to EUR
    $debtorReference = "1234";
    $dueDateTime = null; // set it automatically to two weeks in advance.

    // To create AND perform a request:
    $request = $bluem_object->CreatePaymentRequest(
        $description,
		$debtorReference,
		$amount,
		$dueDateTime,
        $currency
    );
    $response = $bluem_object->PerformRequest($request);

    // Or, to create and perform a request together in shorthand:
    $response = $bluem_object->Payment(
        $description,
		$debtorReference,
		$amount,
		$dueDateTime,
        $currency
    );
    
```

### Requesting a payment status:
Similarly to requesting a Mandate status:

```php
$bluem_object->PaymentStatus($transactionID, $entranceCode);
```



## Identity (iDIN)

### Configuring iDIN

Make sure that there is a `IDINBrandID` property set in config to utilize iDIN in parallel to other Bluem services.
The BrandID of different services might differ: e.g. CompanyPayment, CompanyMandate and CompanyIdentity.
Set it like this:
```php
$config->IDINBrandID = "CompanyIdentity";
```


### Identity request types explained

There are several possible IdentityRequests. One or more request types can be accessed simultaneously.
On a successful response, the details within each type will be returned by the bank for further processing on your side.
The possibilities are:

```json
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
```

For information on these specific request types, a more comprehensive guide will follow soon. For now, contact your Bluem account manager for assistance in choosing the use case that matches your desired activities.

The three most commonly used cases with Identity requests are: 

- Full identification (request 1 or multiple of: `Name`, `Address`, `BirthDate`, `Gender`, `EmailAddress`, `PhoneNumber`, `CustomerID`)
- Age verify 18+ (request `AgeVerify`)
- Safe login with iDIN (request `CustomerIDLogin`)



### Creating an identity request

Creating an Identity Transaction Request can be done after the Bluem object has been properly instantiated. 
Keep in mind that the BrandID has to be compatible with Identity requests. Usually the corresponding brand ID ends with "Identity" instead of for example "Mandate" or "Payment".

The `$returnURL` is vital, as Bluem will redirect the user to that location after the process within the portal is done. The next section deals with creating the callback function.

```php
$description = "Test identity"; // description is shown to customer
$debtorReference = "1234"; // client reference/number
$returnURL = "https://yourdomain.com/integration/identity.php?action=callback"; // provide a link here to the callback function; either in this script or another script

$request = $bluem_object->CreateIdentityRequest(
	["BirthDateRequest", "AddressRequest"],
	$description,
	$debtorReference,
	$returnURL
);

$response = $bluem_object->PerformRequest($request);
```

Handling the request from then is straightforward. Keep in mind to save the returned information somewhere in the session or user data store so you can easily refer back to this identity request later. Note: It could be wise also to save what type of request you have executed to know what to do with it later.

```php
if ($response->ReceivedResponse()) {

	$entranceCode = $response->GetEntranceCode();
	$transactionID = $response->GetTransactionID();
	$transactionURL = $response->GetTransactionURL();
	// save this somewhere in your data store

	$_SESSION['entranceCode'] = $entranceCode;
	$_SESSION['transactionID'] = $transactionID;
	$_SESSION['transactionURL'] = $transactionURL;

	// direct the user to this place
	header("Location: ".$transactionURL);

} else {
	// no proper response received, tell the user
}

```

### The Identity Response callback

Processing the Identity transaction callback function can be done after the Bluem object has been properly instantiated. 

```php
// retrieve from a store, preferably more persistent than session. 
// this is purely for demonstrative purposes
$transactionID = $_SESSION['transactionID'];
$entranceCode = $_SESSION['entranceCode'];


$statusResponse = $bluem_object->IdentityStatus(
    $transactionID,
    $entranceCode
);

```

Now, based on the response, you can take action:

```php
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
```



## Iban Check

Specific instructions coming soon..

## Important notes

### Enable secure Webhook reception through a certificate check
To be able to use webhook functionality, retrieve a copy of the Webhook certificate provided by Bluem and put it in a folder named `keys`, writeable by the code in this library.

