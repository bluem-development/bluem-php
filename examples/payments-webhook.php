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
 * http://localhost/code.php?action=webhook
 * 
 * change this URL to match your webserver and code location
 */
if ($_GET['action'] === "webhook") {
    
    // if you want debug information and verbose results when testing the webhook, set this to true
    $bluem_object->setConfig("webhookDebug", false);
    
    // this call will exit with a 200 or 400 HTTP status code, and perform the necessary work for you
    $bluem_object->Webhook();
}
