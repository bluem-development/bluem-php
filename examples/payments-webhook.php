<?php

/**
 * Bluem-PHP examples: Webhook for payments
 * This file contains examples and annotations for using the `bluem-php` package.
 * All to-dos are for your reference where action on your part is still required.
 *
 * Code is courtesy of and property of Bluem Payment Services
 * Author: Bluem Plugin Support (pluginsupport@bluem.nl)
 */

require_once __DIR__.'/initialization.php';

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
