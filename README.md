# bluem-php

A PHP interface for utilizing the Bluem services such as eMandate, ePayments, iDIN and more.

Utilized by a range of other applications such as WordPress and WordPress WooCommerce plugins 
% add links %

Use this to write your own applications in PHP that communicate with Bluem.

## Installation

Run Composer to install this library and dependences:

```bash
composer require daanrijpkema/bluem-php:dev-master
```

## Configuration

Include the required autoload functions of Composer in your code. 

```php
require 'vendor/autoload.php';
```

Then you can retrieve an object to utilize all functions as such:

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

$bluem_object = new BlueMIntegration($bluem_config);
```

## Usage

### Creating an eMandate Transaction
Creating necessary variables can be done using helper functions

Generating an entrance code (required for later retrieving statuses)

```php
$entranceCode = $this->bluem->CreateEntranceCode();
```
Generating a mandate ID:
```php
$mandateId = $this->bluem->CreateMandateId($order_id, $customer_id);
```
When creating a new transaction,  the entranceCode and MandateID will be generated implicitly.

#### Creating Simple transactions
When you are handling a callback and status update yourself, you can use the simple transaction type. This simply creates a transaction, tells you where to redirect. After the user finishes the transaction process, they are redirected to the fourth parameter without any further ado.
```php
// simple emandate transaction
$response = $this->bluem->CreateNewTransaction($customer_id, $order_id,"simple","https://google.com");
```

#### Creating Default transactions
The default transaction returns to a callback function at a specific URL that then automatically performs a Status Update and can perform further functionalities.
It uses the `merchantReturnURLBase` attribute, set in the parameter when creating the `$bluem_object` object to know where to redirect to expect this function.
This process automatically adds the mandateID as a GET parameter to the return URL, so it can be picked up for the Status Update.
```php
// default
$response = $this->bluem->CreateNewTransaction($customer_id, $order_id,"default");
```

#### Redirection after creation
When you have created a transaction, you receive a response from Bluem telling you where to redirect the user to.
```php
if (isset($response->EMandateTransactionResponse->TransactionURL)) {
    $transactionURL = ($response->EMandateTransactionResponse->TransactionURL . "");
    // TODO: redirect to the transaction URL
} else { 

    // TODO: no proper status given, show an error.
}
```


### Requesting a Transaction status

```php
$response = $this->bluem->RequestTransactionStatus(
    $existing_mandate_id,
    $existing_entrance_code
);
if (!$response->Status()) {
    // no valid response received
} else {
    if ($response->EMandateStatusUpdate->EMandateStatus->Status . "" === "Success") {
        // successful status response
    } else {
        // different status response
    }
}
```


## Important notes

### Enable secure Webhook reception through a certificate check
To be able to use webhook functionality, retrieve a copy of the Webhook certificate provided by Bluem and put it in a folder named `keys`, writeable by the code in this library.
