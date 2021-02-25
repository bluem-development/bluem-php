<?php 
/**
 * Bluem-PHP examples: initialisation
 * This file contains examples and annotations for using the `bluem-php` package.
 * All to-do's are for your reference where action on your part is still required.
 * 
 * Code is courtesy of and property of Bluem Payment Services
 * Author: Daan Rijpkema (info@daanrijpkema.com)
 */


// @todo require composer here instead of directly requiring the file, by uncommenting this line and commenting/removing the one below
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__.'/../src/Integration.php';

use Bluem\BluemPHP\Integration;

$bluem_config = new Stdclass();

/** 
 * Creating the right configuration, with documentation: 
 * */

$bluem_config->environment = "test" ;                // Fill in "prod", "test" or "acc" for production, test or acceptance environment.
$bluem_config->senderID = "";                        // The sender ID, issued by Bluem. Starts with an S, followed by a number.
$bluem_config->test_accessToken = "";                // The access token to communicate with Bluem, for the test environment.
$bluem_config->production_accessToken = "" ;         // The access token to communicate with Bluem, for the production environment.
$bluem_config->merchantID = "" ;                     // the PRODUCTION merchant ID, to be  found on the contract you
													 // have with the bank for receiving direct debit mandates.
// NOTE that MerchantID for test environment is set automatically to a valid test value
$bluem_config->brandID = "";                         // What's your BrandID? Set at Bluem
$bluem_config->expectedReturnStatus = "success" ;    // What status would you like to get back for a TEST transaction or status request? Possible values: none, success, cancelled, expired, failure, open, pending
$bluem_config->eMandateReason = "eMandateReason" ;   // Brief description of the debt collection at the time of issue
$bluem_config->localInstrumentCode = "B2B" ;         // Choose type of collection: CORE or B2B
$bluem_config->merchantReturnURLBase = "https://website.com";;  // URL to return to after finishing the process

// If you are using iDIN next to other services, you can set a specific iDIN brandID here: 
$bluem_config->IDINbrandID = "";

/** Initialize */
$bluem_object = new Integration($bluem_config);

