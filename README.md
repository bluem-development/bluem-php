![Bluem](https://bluem.nl/img/BluemAboutIcon.svg)

## Table of Contents
* [Notes per version](#notes-per-version)
* [Testing](#testing)
* [Frequently asked questions](#frequently-asked-questions)
* [Installation](#installation)
* [Configuration](#configuration)
* [General concept](#general-concept)
* [Payments](#payments)
    + [Create a payment transaction](#create-a-payment-transaction)
    + [Requesting a payment status](#requesting-a-payment-status)
    + [Tip for testing with payments](#tip-for-testing-with-payments)
    + [Adding additional data to a request](#adding-additional-data-to-a-request)
        - [EmailAddress](#emailaddress)
        - [MobilePhoneNumber](#mobilephonenumber)
        - [CustomerProvidedDebtorIBAN](#customerprovideddebtoriban)
        - [CustomerNumber](#customernumber)
        - [CustomerName](#customername)
        - [AttentionOf](#attentionof)
        - [Salutation](#salutation)
        - [CustomerAddressLine1](#customeraddressline1)
        - [CustomerAddressLine2](#customeraddressline2)
        - [DebtorBankID](#debtorbankid)
        - [DynamicData](#dynamicdata)
* [eMandates](#emandates)
    + [Mandate specific configuration fields](#mandate-specific-configuration-fields)
    + [Creating an eMandate Transaction - helper functions](#creating-an-emandate-transaction--helper-functions)
        - [Creating an eMandate transaction](#creating-an-emandate-transaction)
        - [Redirection after eMandate transaction creation](#redirection-after-emandate-transaction-creation)
    + [Requesting an eMandate Transaction status](#requesting-an-emandate-transaction-status)
* [Identity - iDIN](#identity----idin)
    + [Configuring iDIN](#configuring-idin)
    + [Identity request types explained](#identity-request-types-explained)
    + [Creating an identity request](#creating-an-identity-request)
    + [The Identity Response callback](#the-identity-response-callback)
    + [Testing identity return statuses](#testing-identity-return-statuses)
    + [CustomerIDLoginRequest](#customeridloginrequest)
* [IBAN-Name check](#iban-name-check)
    + [Use-cases for IBAN-Name checking and Edge cases](#use-cases-for-iban-name-checking-and-edge-cases)
* [Important miscellaneous notes](#important-miscellaneous-notes)
    + [Enable secure Webhook reception through a certificate check](#enable-secure-webhook-reception-through-a-certificate-check)
* [Appendices](#appendices)
    + [List of all supported BICs per context](#list-of-all-supported-bics-per-context)
        - [ePayments](#epayments)
        - [eMandates CORE](#emandates-core)
        - [eMandates B2B](#emandates-b2b)
        - [Identity](#identity)
        

# Bluem-php for Payment, Mandates, iDIN & IBAN-Name check

A PHP interface for utilizing the Bluem services ePayments, eMandates, iDIN and/or IBAN-Name check.
Utilize this library to write your own applications in PHP that communicate with Bluem, without having to handle the flow yourself.

Utilized by other applications as well:

- [WordPress and WooCommerce plug-in](https://github.com/bluem-development/bluem-woocommerce), available for Bluem customers.
- [Magento2 module](https://github.com/bluem-development/bluem-magento/), available for Bluem customers.

## Getting started:
This library can be installed through [Composer](https://getcomposer.org). Run Composer to install this library and dependencies from your project folder.

```bash
composer require bluem-development/bluem-php
```
---

Refer to the [examples folder](https://github.com/bluem-development/bluem-php/tree/master/examples) for a full example implementation that you can base your own integration on. 

Please contact us if you have any questions regarding the examples or the implementation of the library in your project.

## Requirements

- PHP 7.0+ is required for all versions of this library. Please use the major git releases for the stable versions of this plugin. 
- Refer to the `composer.json` requirements for any other dependencies

## Notes per version
### Version 2.1 (latest)

Major improvement in code style.
- Added `$bluem->getConfig($key)` method to retrieve a configuration value.
- Added `$bluem->setConfig($key, $value)` method to set a configuration value.
- Added several validation steps
- Added more unit testing coverage
- Separated more responsibilities for cleaner code

### Version 2.0.12 
Allowing the verification if the current IP is based in the Netherlands utilizing a geolocation integration *(IPAPI). Use this feature through the Helper/IPAPI class. 
Currently, implemented public function is: 
```php
$bluem->VerifyIPIsNetherlands();
// returns bool true if NL or error, returns false if no error and other country
```
*This feature is useful when determining whether to use IDIN identity checking in any application, as this is currently implemented only for Dutch banks.*

### Version 2.0.2:

Triodos Bank, BIC TRIONL2U no longer supported for Identity requests as of 1 june 2021. See: https://www.triodos.nl/veelgestelde-vragen/kan-ik-idin-gebruiken?id=4de127e85eee

- If you use the [Preselection of banks using the DebtorWallet](https://github.com/bluem-development/bluem-php#debtorwallet-preselecting-a-bank-for-mandate-payment-or-identity-request), you will have to update this library to ensure Triodos is no longer an option for iDIN. If you do not do this, customers that select Triodos will be presented with an error.

- If you use the Bluem portal, you don't need to take action. This change is already applied within the Bluem portal.


### Version 2.0.1:
Major release with more stability, validation and features.

Please note: The main Integration class is called Bluem, so to include it, use:
```php
$bluem = new Bluem($config);
```
Or use a class alias to ensure code functioning. This is a refactor since version 1.x.

Furthermore, all generally available functions are still available.

### Versions before 2.0
No changelog was recorded. Please refer to the [commit log](https://github.com/bluem-development/bluem-php/commits/master) for more information



## Testing
For improving future features, unit testing is introduced since november 2021.

Tests are located in the `tests` folder
To run tests:
```
./vendor/bin/phpunit 
```
Testing is done given a `.env` file. Please ensure that a filled `.env` file is available. A `.env.sample` file is provided to help you configure it.

### Description of the base tests:
- Testing if requests can be created 
- Testing if entrance codes can be generated

This is currently focused around unit testing to see if new changes break the system.



## Frequently asked questions

*I get the message "Unauthorized: Check your account credentials" - what should I do?*<br>
Please ensure that your SenderID, BrandID and Tokens for Test and/or Production environments are set correctly. Usually this message retains to an invalid configuration OR an unactivated account. 
If you have checked that the credentials are correct, but you still receive this message, please contact your Bluem account manager.

*How can I make the experience of identifying more user-friendly?*<br>
- You can reduce the amount of steps required by performing the selection of the bank within your own application and interface by utilizing the preselection feature from the PHP library on the request object as so:

```php
    $BIC = "INGBNL2A";
    $request->selectDebtorWallet($BIC);
```
See the section **DebtorWallet: preselecting a bank for Mandate, Payment or Identity request** for more information.
This method can be used when creating iDIN and when creating iDEAL requests; you could store the selected bank ("Issuer") on user level and use it when creating a request for your user.
- You can inform the user WHY this is necessary and refer to the new laws and rules, in your own website/application or refer to the news/public announcements.
- You can inform the user about the amount of trouble required: display a piece of text saying that it only takes a minute or two, and that it is stored for your convenience, and that it ensures integrity and a valid webshop experience.


*Can I connect the Identity service with a payment service like Emandates or iDEAL so the user is only redirected once?*<br>
- No, you cannot, as these are separate processes.





## Configuration

Include the required autoload functions of Composer in your code, if you did not do so already through other dependencies.

```php

// get composer dependencies
require_once __DIR__. '/vendor/autoload.php';

// then use the library in the top of your script(s).
use Bluem\BluemPHP\Bluem;

```

Then you can retrieve an object to utilize all functions as below. It is suggested to save these configuration settings in a database or settings handler, so they can be stored by the user instead of in code.

```php
$config = new Stdclass();

// Fill in prod, test or acc for production, test or acceptance environment.
$config->environment = ...

// The sender ID, issued by Bluem. Starts with an S, followed by a number.
$config->senderID = ... 

// The access token to communicate with Bluem, for the test environment.
$config->test_accessToken = ... 

// The access token to communicate with Bluem, for the production environment.
$config->production_accessToken = ... 

// the merchant ID (for eMandates), to be found on the contract you have with the bank for receiving direct debit mandates.
$config->merchantID = ...

// The slug of the 'Thank You' page to which should be referred after completing process. If your Order ID is processed in the URL it will be filled in for you
$config->thanksPage = ...
// Not applicable for IBAN-Name check

// What status would you like to get back for a TEST transaction or status request? Possible values: none, success, cancelled, expired, failure, open, pending
$config->expectedReturnStatus = ...
// Not applicable for IBAN-Name check

// What's your BrandID? Set at Bluem
$config->brandID = ...
// Not applicable for IBAN-Name check

// eMANDATES Specific:
// Brief description of the debt collection at the time of issue
$config->eMandateReason = ...

// Choose type of collection: CORE or B2B
$config->localInstrumentCode = ...

// URL to return to after finishing the process
$config->merchantReturnURLBase = ...;
// Not applicable for IBAN-Name check

$bluem = new Bluem($config);
```
If parts of the Bluem object are not instantiated correctly, the instantiation might throw an exception.


## General concept

This library makes it easy to perform communication with Bluem for its services (ePayments, eMandates, iDIN and IBAN-Name Check). The flow for each service is very similar and thus easy to understand once you understand and implement one of them.

1. **TransactionRequest (from site to Bluem)**: Your application creates a request object, sends it to Bluem's servers with authentication.
2. **TransactionResponse (including TransactionURL) (from Bluem to site)**: Your application receives a response with a URL as entry point into the Bluem environment, if the request was successful.
3. **Bluem Environment**: Redirect client to TransactionURL, client (via checkout) to bank, confirm transaction, back to Merchant (via MerchantReturnURL with Emandates, and via DebtorReturnURL with Payments/Identity), this is trigger for StatusRequest
The user is redirected to the response URL at the Bluem servers. In this Bluem environment, the user performs a payment/mandate signing or identity/IDIN check and returns to a predefined URL.
4. **StatusRequest (from site to Bluem)**: Using this same package one can check the Status of a request using a second endpoint (given an ID and entranceCode of the transaction defined at the creation of the request). This is vital, as it allows you to change the status of an order or check within your site or app based on Bluem's status. It is recommended to do this check when the user comes back to your site/app directly after handling the transaction at Bluem AND using a webhook functionality.
5. **StatusUpdate (from Bluem to site)**: This response object that comes back from a callback or webhook, contains an updated status that you can process within your application. I.e. when the user has paid or verified, and you have to change a product, order or process' status and go to a next step.
6. **Webhook**: The webhook functionality allows Bluem to directly push status changes and transaction results to your site or app in a trustworthy way. Therefore, your orders and transactions will always get updated to the corresponding statuses, no matter what your user does after visiting Bluem's transaction page.

The Webhook is only needed for ePayments and eMandates: online stores/portals that need to know directly the status, for those cases that client closes the browser at a bank after successful confirmation of transaction. Webhook not needed for iDIN as with iDIN the client ALWAYS comes back to website after successful identification (there is no place where the end user can close the browser).

**Using the webhook functionality is highly recommended.** More instructions on implementing the webhook will follow in each specific service shortly.

Please note that the flow for the IBAN-Name check is shorter: Only a TransactionRequest and the results come directly back in the TransactionResponse. This is as the end-user is not needed; the call is straight to the Bank Database, that provides in the TransactionResponse the IBAN-Name check results. 

### DebtorWallet - Preselecting a bank for Mandate, Payment or Identity request 

It is possible to preselect a Bank within your own application based on an IssuerID (BIC/Swift code) when creating a Mandate, Payment or Identity request. This can be used if you want to user to select the given bank in your own interface and skip the bank selection within the Bluem portal interface.

To preselect a bank in a request, use the following function on a request object (supported for Mandates, Payments and Identity only) for an example BIC:

```php
$BIC = "INGBNL2A";
$request->selectDebtorWallet($BIC);
```
Parameter has to be a valid BIC code of a supported bank. An invalid BIC code will trigger an exception. **Please note that supported BICs differ per service as not every bank offers the same services!** The supported BIC codes per service can also be requested from a Bluem object, given the service context. **As appendix to this Documentation file, you can find a full list of all BICs per context.**

Illustrated here is that a list of BICs per context can also be retrieved programmatically:

```php
$MandatesBICs = $bluem->retrieveBICsForContext("Mandates"); // also specific to localInstrumentCode, see notes.
$PaymentsBICs = $bluem->retrieveBICsForContext("Payments");
$IdentityBICs = $bluem->retrieveBICsForContext("Identity");
```
Input of a different context will trigger an exception. If valid, the result is an array of `Bluem\BluemPHP\BIC` objects with attributes `IssuerID` and `IssuerName`: the BIC and Bank name respectively. You can use this to populate your user interface.

Please note that the BIC list will vary when a different `localInstrumentCode` is configured. The localInstrumentCode `CORE` and `B2B` are supported by different banks. Based on your configuration, the right BIC list is loaded from context automatically and used to verify the debtorWallet.

## Webhooks

Webhooks exist for Payments, eMandates and Identity. They trigger during requests to the Bluem flow and send data to your application.
They are vital to ensure all processes are always completed, even if the customer/user does not reach your regular callback method(s) in your flow. 

Activate the endpoints by creating an endpoint for testing and production environments. 
They then need to be configured on Bluem's side and implemented on the client side.

### 1. How to implement
Create an HTTP-reachable endpoint for TEST and PRODUCTION environments. Examples are:

- [https://example.com/webhook/test](https://example.com/webhook/test)
or [https://example.com/webhook_test.php](https://example.com/webhook/test)
- [https://example.com/webhook/production](https://example.com/webhook/production)
- etc.

Call the `Webhook()` function within that endpoint:

```php
$bluem_object->setConfig("webhookDebug", false);
$webhook = $bluem_object->Webhook();

// implement this like you implemented the callback
// for the regular services in your application
if ($webhook !== null) {     
    if ($webhook->getStatus() === "Success") {
        // deal with the successful callback
    }
    // ...etc
}
```
Refer to the `examples/payments-webhook.php` for a more in-depth example implementation and retrieval of the data when a webhook is received in your application. 


You can utilize the following functions on the callback object to get the data from the webhook and process the relevant data. 

_Note_: Most functions return string values, unless otherwise specified.

**Functions available in all services:**

```php
$callbackObject->getEntranceCode();
$callbackObject->getPaymentReference();
$callbackObject->getCreationDateTime();
$callbackObject->getStatus();
$callbackObject->getDebtorReference();
```

**Payments specific functions:**
```php
$transactionID = $webhook->getTransactionID();
$amount = $webhook->getAmount();
$amountPaid = $webhook->getAmountPaid();
$currency = $webhook->getCurrency();
$paymentMethod = $webhook->getPaymentMethod();

// note: these return a SimpleXML object
$paymentMethodDetails = $webhook->getPaymentMethodDetails(); 
$iDealDetails = $webhook->getIDealDetails();

// note: these are currently iDEAL specific
$debtorAccountName = $webhook->getDebtorAccountName();
$debtorIBAN = $webhook->getDebtorIBAN();
$debtorBankID = $webhook->getDebtorBankID();
```

**EMandates specific functions:**
```php
$mandateID = $webhook->getMandateID();
$statusDateTime = $webhook->getStatusDateTime();
$originalReport = $webhook->getOriginalReport(); // note: returns raw XML cdata object that still needs to be parsed
$acceptanceReport = $webhook->getAcceptanceReportArray(); // note: returns array with a lot of values that are of use
```

**Identity specific functions:**

```php
$requestType = $webhook->getRequestType();
$transactionID = $webhook->getTransactionID();
$debtorReference = $webhook->getDebtorReference();
$authenticationAuthorityID = $webhook->getAuthenticationAuthorityID();
$authenticationAuthorityName = $webhook->getAuthenticationAuthorityName();

// note: returns array with a lot of values that are of use
$identityReportArray = $webhook->getIdentityReportArray(); 
```

### 2. How to configure
**Communicate the endpoint URLs to [pluginsupport@bluem.nl](mailto:pluginsupport@bluem.nl?subject=Bluem+Webhook+Endpoints) to have these URLs be configured in your account.**
Please allow for a few working days for this to be configured. We will follow-up as soon as the endpoints have been added.

### 3. Verify that it works
You can POST to your own endpoints to verify that it works. Please contact us for a sample Webhook call that you can use for your service.

### Support for webhooks
We can help you troubleshoot any problems you might face after creating the endpoints. We can also help you verify that data gets sent properly.
Please contact us if you need assistance in this regard.

## Payments

The following attributes in the config are vital for proper eMandate functionality:

- `PaymentReference`: a reference visible within the administration, which can be used to identify the customer and the transaction details.
- `Amount` 
  - Amount Mutable
  - MinAmount
  - MaxAmount
  - AmountArray,
- A valid `brandID` set and enabled for payments.

### Creating a payment transaction

The Payments service is like the eMandates service, but utilises other parameters. Here is an example:

```php
$description = "Test payment"; // a concise description with possible references to order name and such
$amount = 100.00;	 // as a float
$currency = "EUR"; // if set to null, will default to EUR as string
$debtorReference = "1234023"; 
$dueDateTime = null; // set it automatically a day in advance. if you want to set it, use a datetime string in "YYYY-MM-DD H:i:s" format

$entranceCode = $bluem->CreateEntranceCode();

// To create AND perform a request:
$request = $bluem->CreatePaymentRequest(
    $description,
    $debtorReference,
    $amount,
    $dueDateTime,
    $currency,
    $entranceCode
);
// Or, to create and perform a request together in shorthand:
$response = $bluem->Payment(
    $description,
    $debtorReference,
    $amount,
    $dueDateTime,
    $currency
);
```

### Requesting a payment status
Similarly to requesting a Mandate status (see below):

```php
$statusResponse = $bluem->PaymentStatus($transactionID, $entranceCode);

if ($statusresponse->ReceivedResponse()) {

    $statuscode = ($statusresponse->GetStatusCode());

    // add your own logic in each case:
    switch ($statuscode) {
        case 'Success':
            // do something when the payment is successful directly
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

}
```

### Tip for testing with payments
You can, in Bluem test mode, place iDEAL orders of a specific amount, to get a certain status back. This way you can see how it works if a payment succeeds, or if it fails. The available statuses are:

- `1.00` (or any other value) Success
- `4.00` Open
- `2.00` Cancelled
- `3.00` Expired
- `5.00` Failure
- `7.00` Failure in system


### Adding additional data to a request

You can add additional information to a request object *before* performing it, which can be useful. The data will be stored within the ViaMijnBank Portal for further administrative purposes.

```php
$key = "EmailAddress";
$value = "john@doe.com";

// after instantiating the request
$request->addAdditionalData($key, $value);
// but before performing it
```
Providing a key that is not in the above list of options or a value that does not match the expected format for each option will trigger an exception.

The key options are described below:

#### EmailAddress
Include an email address of the customer.

#### MobilePhoneNumber
Include an email address of the customer.

#### CustomerProvidedDebtorIBAN
Include an additional Debtor IBAN address of the customer.

#### CustomerNumber
Include a customer number.
#### CustomerName
Include a customer name.
#### AttentionOf
Include a customer title or name of person to be addressed

#### Salutation
Include a customer title or name of person to be saluted

#### CustomerAddressLine1
Include a first part of an address of the customer.
#### CustomerAddressLine2
Include a second part of an address of the customer.

#### DebtorBankID
More instructions follow.

#### DynamicData
More instructions follow.



## eMandates


### Mandate specific configuration fields
The following attributes in the bluem_config are vital for proper eMandate functionality. Below is a short description what each configuration field means:

Local Instrument Code (CORE, B2B)

RequestType 
- Issuing is the default. Amendment/Cancellation are not actually used in practice at this time.

SequenceType: 
  - One-off, or`OOFF`
  - Recurring, or `RCUR`

MandateID,

MerchantID,

MaxAmount (for B2B),

ValidationReference

### Creating an eMandate Transaction - helper functions
You need certain information to reference a transaction request: an ID (in this case the MandateID) and an entranceCode (basically a timestamp when you started the request). Creating this information can be done using helper functions. When creating a new transaction,  the entranceCode and MandateID will be generated within the `$bluem`.


Generating a mandate ID:
```php
$mandateId = $bluem->CreateMandateId($order_id, $customer_id);
```

Generating an entrance code:

```php
$entranceCode = $bluem->CreateEntranceCode();
```

#### Creating an eMandate transaction
The default transaction returns to a callback function at a specific URL that then automatically performs a Status Update and can perform further functionalities.
It uses the `merchantReturnURLBase` attribute, set in the parameter when creating the `$bluem` object to know where to redirect to expect this function.
This process automatically adds the mandateID as a GET parameter to the return URL, so it can be picked up for the Status Update.

```php
// default
$request = $bluem->CreateMandateRequest($customer_id,$order_id,"default");

// After creating any request, you will still have to perform the request:

$response = $bluem->PerformRequest($request);
```

Tip: you can also combine the creation and performance if the request in one function call, if you do not want to manipulate or read the request object beforehand:
```php
$response = $bluem->Mandate($customer_id, $order_id,"default");

```

If you do anything wrong, or you are unauthorized, the Response object will be of type `Bluem\BluemPHP\ErrorBluemResponse` and has an `Error()` function to retrieve a string of information regarding your error that you could display to your user or handle yourself.

An example about incorrect access token and Ids could be: "Unauthorized: check your access credentials".

#### Redirection after eMandate transaction creation
When you have performed a transaction request successfully, you receive a response object from Bluem. This object tells you where to redirect the user to, to actually perform the administrative steps at the Bluem portal.
```php
if ($response->ReceivedResponse()) {

	$entranceCode = $response->GetEntranceCode();
    // save the entranceCode in your data store

	$transactionURL = $response->GetTransactionURL();

    // TODO: redirect to the above transaction URL
    header("Location: ". $transactionURL); // or something of the sort.
} else {
    // TODO: no proper status given, show an error.
    exit("Error: " . $response->Error()); // for example
}
```

### Requesting an eMandate Transaction status

```php
$response = $bluem->MandateStatus(
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
	"EmailRequest",
	"AgeCheckRequest",
    "CustomerIDLoginRequest"
]
```

For information on these specific request types, a more comprehensive guide will follow soon. For now, contact your Bluem account manager for assistance in choosing the use case that matches your desired activities.

The three most commonly used cases with Identity requests are:

1. Full identification (request 1 or multiple of: `Name`, `Address`, `BirthDate`, `Gender`, `EmailAddress`, `PhoneNumber`, `CustomerID`). 
This is used for KYC, Wwft, AML compliance and for Account Creation.
2. Age verify 18+ (request `AgeVerify`) **and cannot be combined with the other request categories**.
3. Safe login with iDIN (request `CustomerIDLogin`) **and cannot be combined with the other request categories**; Here you utilize iDIN login as an alternative safe login method next to a traditional username – password login. Please note that use of Safe login with iDIN requires the first time a Full identification.



### Creating an identity request

Creating an Identity Transaction Request can be done after the Bluem object has been properly instantiated.
Keep in mind that the BrandID has to be compatible with Identity requests. Usually the corresponding brand ID ends with "Identity" instead of for example "Mandate" or "Payment".

The `$returnURL` is vital, as Bluem will redirect the user to that location after the process within the portal is done. The next section deals with creating the callback function.

```php
$description = "Test identity"; // description is shown to customer
$debtorReference = "1234"; // client reference/number
$returnURL = "https://yourdomain.com/integration/identity.php?action=callback"; // provide a link here to the callback function; either in this script or another script

$request = $bluem->CreateIdentityRequest(
	["BirthDateRequest", "AddressRequest"],
	$description,
	$debtorReference,
	$returnURL
);

$response = $bluem->PerformRequest($request);
```

Handling the request from then is straightforward. 
Please note: save the returned information somewhere in the session or user data store, so you can easily refer back to this identity request later. Note: It could be wise also to save what type of request you have executed to know what to do with it later.

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
// the code below is purely for demonstrative purposes
$transactionID = $_SESSION['transactionID'];
$entranceCode = $_SESSION['entranceCode'];


$statusResponse = $bluem->IdentityStatus(
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
             *  "DateTime": string(24) "2020-10-16T15:30:45.803Z"
             *  "CustomerIDResponse": string(21) "FANTASYBANK1234567890"
             *  "AddressResponse":
             *  object(Bluem\BluemPHP\IdentityStatusBluemResponse)#4 (5) {
             *      "Street": string(12) "Pascalstreet"
             *      "HouseNumber": string(2) "19"
             *      "PostalCode": string(6) "0000AA"
             *      "City": string(6) "Aachen"
             *      "CountryCode": string(2) "DE"
             *  }
             *  "BirthDateResponse": string(10) "1975-07-25"
             *  */
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

### Testing identity return statuses
When using specific entranceCodes for iDIN starting with a given prefix you  will always get to a test status page of the bank where you can choose which status you want to receive back. You can easily utilize this feature by altering your request object.

**Please note that this only works in the TEST environment.**

```php
$request->enableStatusGUI();
```

Advanced note: This function prepends a string to your entranceCode. This might clip your original entranceCode if it exceeds the max length.

### CustomerIDLoginRequest

It is also possible to use this library for performing a login request for your application, using a customized flow.
Detailed instructions will follow here shortly.


## IBAN-Name check

This service allows you to verify if a name and IBAN combination are matching and valid, which can be useful when you are validating user registration, input or within a checkout procedure. A simple example of such a request and its result can be found below:

```php


$iban = "NL99ABNA1234012428";
$name = "H. de Vries";
$debtorReference = "1234";

$request = $bluem->CreateIBANNameCheckRequest(
    $iban,
    $name,
    $debtorReference
);

$response = $bluem->PerformRequest($request);

switch ($response->GetIBANResult()) {
case 'INVALID':
    echo "IBAN $iban and name $name do not match";
    break;
case 'KNOWN':
    // handle how the response should be taken in
    break;
}
```

### Use-cases for IBAN-Name checking and Edge cases
More details on how to use IBAN-Name checking will follow shortly.


## Important miscellaneous notes

### Enable secure Webhook reception through a certificate check
To be able to use webhook functionality, retrieve a copy of the Webhook certificate provided by Bluem and put it in a folder named `keys`, writeable by the code in this library.

## Appendices
### List of all supported BICs per context
#### ePayments
- ABN AMRO  <BR> BIC: `ABNANL2A`
- ASN Bank  <BR> BIC: `ASNBNL21`
- bunq  <BR> BIC: `BUNQNL2A`
- Handelsbanken <BR> BIC: `HANDNL2A`
- ING   <BR> BIC: `INGBNL2A`
- Knab  <BR> BIC: `KNABNL2H`
- Moneyou   <BR> BIC: `MOYONL21`
- Rabobank  <BR> BIC: `RABONL2U`
- RegioBank <BR> BIC: `RBRBNL21`
- SNS   <BR> BIC: `SNSBNL2A`
- Triodos Bank  <BR> BIC: `TRIONL2U`
- Van Lanschot  <BR> BIC: `FVLBNL22`
- Revolut   <BR> BIC: `REVOLT21`
#### eMandates CORE
- ABN AMRO  <BR> BIC: `ABNANL2A`
- ASN Bank  <BR> BIC: `ASNBNL21`
- ING   <BR> BIC: `INGBNL2A`
- Knab  <BR> BIC: `KNABNL2H`
- Rabobank  <BR> BIC: `RABONL2U`
- RegioBank <BR> BIC: `RBRBNL21`
- SNS   <BR> BIC: `SNSBNL2A`
- Triodos Bank  <BR> BIC: `TRIONL2U`
#### eMandates B2B
- ABN AMRO  <BR> BIC: `ABNANL2A`
- ING   <BR> BIC: `INGBNL2A`
- Rabobank  <BR> BIC: `RABONL2U`

#### Identity
- ABN AMRO  <BR> BIC: `ABNANL2A`
- ASN Bank  <BR> BIC: `ASNBNL21`
- bunq  <BR> BIC: `BUNQNL2A`
- ING   <BR> BIC: `INGBNL2A`
- Rabobank  <BR> BIC: `RABONL2U`
- RegioBank <BR> BIC: `RBRBNL21`
- SNS   <BR> BIC: `SNSBNL2A`

Please note: Triodos Bank with BIC: `TRIONL2U` does not support Identity anymore as of 1st of June 2021.
