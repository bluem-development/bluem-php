# bluem-php

A PHP interface for utilizing the Bluem services such as eMandate, ePayments, iDIN and more.

Utilized by a range of other applications such as WordPress and WordPress WooCommerce plugins 
% add links %

Use this to write your own applications in PHP that communicate with Bluem.

## Installation

Run Composer to install this library and dependences:

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

One creates a request object, sends it to Bluem's servers with authentication and receives a response with a URL to redirect the user to (if the request was successful). 

The user is redirected to the response URL at the Bluem servers. In this Bluem environment, the user performs a mandate signing, payment, identity or iban check and returns to a predefined URL. 

Using this same package one can check the Status of a request using a second endpoint (given an ID and entranceCode of the transaction defined at the creation of the request). This is vital, as it allows you to change the status of an order or check within your site or app based on Bluem's status. It is recommended to do this check when the user comes back to your site/app directly after handling the transaction at Bluem AND using a webhook functionality. 

The webhook functionality allows Bluem to directly push status changes and transaction results to your site or app in a trustworthy way. Therefore your orders and transactions will always get updated to the corresponding statuses, no matter what your user does after visiting Bluem's transaction page.

## eMandates 

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



#### Creating Simple eMandate transactions
When you are handling a callback and status update yourself, you can use the simple transaction type. This simply creates a transaction, tells you where to redirect. After the user finishes the transaction process, they are redirected to the fourth parameter without any further ado.
```php
// simple emandate transaction
$request = $bluem_object->CreateMandateRequest($customer_id,$order_id,"simple","https://google.com");
```

#### Creating Default eMandate transactions
The default transaction returns to a callback function at a specific URL that then automatically performs a Status Update and can perform further functionalities.
It uses the `merchantReturnURLBase` attribute, set in the parameter when creating the `$bluem_object` object to know where to redirect to expect this function.
This process automatically adds the mandateID as a GET parameter to the return URL, so it can be picked up for the Status Update.

```php
// default
$request = $bluem_object->CreateMandateRequest($customer_id,$order_id,"default");

```
#### Perform the request
After creating any request, you will still have to perform the request:

```php
$response = $bluem_object->PerformRequest($request);
```

Tip: you can also combine the creation and performance if the request in one function call, if you do not want to manipulate or read the request object beforehand:
```php
$response = $bluem_object->Mandate($customer_id, $order_id,"simple","https://google.com");
$response = $bluem_object->Mandate($customer_id, $order_id,"default");
```

If you do anything wrong or you are unauthorized, the Response object will be of type `Bluem\BluemPHP\ErrorBluemResponse` and has an `Error()` function to retrieve a string of information regarding your error that you could display to your user or handle yourself.

An example about incorrect access token and Ids could be: "Unauthorized: check your access credentials".

#### Redirection after eMandate transaction creation
When you have performed a transaction request successfully, you receive a response object from Bluem. This object tells you where to redirect the user to to actually perform the administrative steps at the Bluem portal.
```php
if (isset($response->EMandateTransactionResponse->TransactionURL)) {
    $transactionURL = ($response->EMandateTransactionResponse->TransactionURL . "");
    // TODO: redirect to the above transaction URL
    header("Location: ". $transactionURL); // or something of thes ort.
} else { 
    // TODO: no proper status given, show an error.
    exit("Error: " . $response->Error()); // for example
}
```

### Requesting an eMandate Transaction status

```php
$response = $bluem_object->RequestTransactionStatus(
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

## Payments

Working similar to eMandates, but with other parameters:

```php
    $description = "Test payment";
    $amount = 100.00;
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

## Identity

Coming soon..

## Iban Check

Coming soon..

## Important notes

### Enable secure Webhook reception through a certificate check
To be able to use webhook functionality, retrieve a copy of the Webhook certificate provided by Bluem and put it in a folder named `keys`, writeable by the code in this library.
