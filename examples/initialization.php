<?php
/**
 * Bluem-PHP examples: initialisation
 * This file contains examples and annotations for using the `bluem-php` package.
 * All to-do's are for your reference where action on your part is still required.
 *
 * Code is courtesy of and property of Bluem Payment Services
 * Author: Daan Rijpkema (info@daanrijpkema.com)
 */


// In your own app: install composer, require the 
// daanrijpkema/bluem-php package 
// and require the vendor/autoload.php file to 
// magically load the library and other depdencies
require_once __DIR__ . '/../vendor/autoload.php';

use Bluem\BluemPHP\Bluem;

$bluem_config = new Stdclass();
/**
 * Creating the right configuration, with documentation:
 * */

// Essential variables, must be filled before the integration works
$bluem_config->environment = "test" ;                //Fill in "prod", "test" or "acc" for production, test or acceptance environment.
$bluem_config->senderID = "";                        // The sender ID, issued by Bluem. Starts with an S, followed by a number.
$bluem_config->brandID = "";                         // What's your BrandID? Set at Bluem and given through email.

// These are required in their respective environment
$bluem_config->test_accessToken = "";                // The access token to communicate with Bluem, for the test environment.
$bluem_config->production_accessToken = "" ;         // The access token to communicate with Bluem, for the production environment.

$bluem_config->expectedReturnStatus = "success" ;    // What status would you like to get back for a TEST transaction or status request? Possible values: none, success, cancelled, expired, failure, open, pending
$bluem_config->merchantReturnURLBase = "https://website.com";  // URL to return to after finishing the process

// NOTE: THE FOLLOWING SETTINGS APPLY FOR IDIN ONLY
// If you do not use iDIN, instantiating this is NOT necessary.
// If you are using iDIN next to other services, you can set a specific iDIN BrandID here:
$bluem_config->IDINBrandID = "";
// if not set, the default brandID will be used.

// NOTE: THE FOLLOWING SETTINGS APPLY FOR EMANDATES ONLY. 
// If you do not use eMandates, instantiating this is NOT necessary.
$bluem_config->merchantID = "" ;                     // the PRODUCTION merchant ID, to be  found on the contract you
// have with the bank for receiving direct debit mandates.
// required for eMandates prod environment
// NOTE that MerchantID for test evironment is set automatically to a valid test value
$bluem_config->eMandateReason = "eMandateReason" ;   // Brief description of the debt collection at the time of issue
$bluem_config->localInstrumentCode = "B2B" ;         // Choose type of collection: CORE or B2B


/** 
 * Initialize 
 **/
// Do note that creating this Bluem object will generate an exception if the configuration is not valid. This applies mostly to the essential configuration variables.
$bluem_object = new Bluem($bluem_config);

