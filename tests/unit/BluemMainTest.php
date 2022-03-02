<?php
namespace Bluem\Tests\Unit;

use Bluem\BluemPHP\Helpers\BluemIdentityCategoryList;
use Bluem\BluemPHP\Helpers\BluemMaxAmount;
use Bluem\BluemPHP\Requests\IdentityBluemRequest;
use Bluem\BluemPHP\Requests\EmandateBluemRequest;
use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;
use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;
use Bluem\BluemPHP\Responses\MandateTransactionBluemResponse;
use Exception;

require_once 'BluemGenericTest.php';

class BluemMainTest extends BluemGenericTest
{
    
    public function testCanCreateMandateRequest()
    {
        $debtorReference = '123456789';
        $current_user_id  ="1";
        $mandate_id = "12134";

        try {
            $request = $this->bluem->CreateMandateRequest(
                $debtorReference,
                $current_user_id,
                $mandate_id
            );
            $this->assertInstanceOf(
                EmandateBluemRequest::class,
                $request
            );
        } catch ( Exception $e ) {
            $this->fail("Could not create mandate request: " . $e->getMessage());
        }

        
//        $this->assertTrue(true, "Can create MandateRequest");
    }
    public function testCanMandate()
    {
        $customer_id = "1";
        $order_id = "1";
        $mandate_id = "12134";

        try {
            $response = $this->bluem->Mandate(
                $customer_id,
                $order_id,
                $mandate_id
            );
        } catch ( Exception $e ) {
            $this->fail("Could not create mandate: " . $e->getMessage());
        }
        // verify response is of the correct type
        $this->assertInstanceOf(
            MandateTransactionBluemResponse::class,
            $response
        );
        
    }
    public function testCanMandateStatus() {
        // params:
        //  ($mandate_id, $entranceCode)
        $mandate_id    = "12134";
        $entranceCode = "121341231";

        try {
            $response = $this->bluem->MandateStatus( $mandate_id, $entranceCode );
        } catch ( Exception $e ) {
            $this->fail("Could not get mandate status: " . $e->getMessage());
        }

        // @todo: deal with the corresponding status if proper or improper status request
        if($response->Status()) {
            
            $this->assertInstanceOf(
                MandateStatusBluemResponse::class,
                $response
            );
        } else {
            $this->assertInstanceOf(
                ErrorBluemResponse::class,
                $response
            );
        }
    }
    public function testCanCreateMandateID()
    {
        // params:
        //  (String $order_id, String $customer_id): String
            $order_id = "1";
            $customer_id = "1";
            
        try {
            $response = $this->bluem->CreateMandateID( $order_id, $customer_id );
        } catch ( Exception $e ) {
            $this->fail("Could not create mandate ID: " . $e->getMessage());
        }
        
        // assert response is string
        
        $this->assertTrue(
            is_string($response),
            "Response is not a string"
        );
        
        // assert response is not empty
        $this->assertNotEmpty(
            $response,
            "Response is empty"
        );
        
        //        $expected_value = 1202201041;
        // assert response is equal to $expected_value
        //        $this->assertEquals(
        //            $response,
        //            "$expected_value",
        //            "Response is not equal to expected value '$expected_value'"
        //        );
        // @todo: deal with the corresponding status if proper or improper status request
    }
    public function testCanGetMaximumAmountFromStatusResponse()
    {
        // params:
        $customer_id = "1"; 
        $order_id = "1";
        $mandate_id = "testB2BRcurCancel";
//        $transaction_response = $this->bluem->Mandate(
//            $customer_id,
//            $order_id,
//            $mandate_id
//        );
    
        // mocked
        $entrance_code = "S00120211025153200802";
        $status_response = $this->bluem->MandateStatus(
            $mandate_id,
            $entrance_code
        );
        
        $this->assertEquals(
            $status_response->error(),
            false,
            "Mandate Status has an error!"
        );
        
// assertions
        $this->assertEquals(
            $status_response->GetMaximumAmount()->amount,
            250.00,
            "Maximum amount is not equal to expected value '250.00'"
        );

        $maxamount = $status_response->GetMaximumAmount();
        
        // assert maxamount is an object
        $this->assertInstanceOf(
            BluemMaxAmount::class,
            $maxamount
        );
        
        $this->assertTrue(
            is_numeric($maxamount->amount),
            "Maxamount is not numeric"
        );

        $this->assertEquals(
            "EUR",
            $maxamount->currency,
            "Maxamount currency is not EUR"
        );
        
    }
    public function testCanCreatePaymentRequest()
    {
        $description = "Test payment";
        $debtorReference = "12134";
        $amount = 12.32;
        $dueDateTime = strtotime("+1 day");
        $currency = "EUR";
        $entranceCode = "121341231";
        $debtorReturnURL = "http://www.google.com";
    
        $request = $this->bluem->CreatePaymentRequest(
            $description,
            $debtorReference,
            $amount,
            $dueDateTime,
            $currency,
            $entranceCode,
            $debtorReturnURL
        );
        
        // assert request is of type PaymentRequest
        $this->assertInstanceOf(
            PaymentBluemRequest::class,
            $request
        );

        //@todo build this test
        $this->assertTrue(true, "Can Create PaymentRequest");
    }
    public function testCanPayment()
    {
        // params:
        //  @todo add parameter list from documentation

        //@todo build this test
        $this->assertTrue(true, "Can Payment");
    }
    public function testCanPaymentStatus()
    {
        // params:
        //  ($transactionID, $entranceCode)

        //@todo build this test
        $this->assertTrue(true, "Can PaymentStatus");
    }
    public function testCanCreatePaymentTransactionID()
    {
        // params:
        //  (String $debtorReference): String

        //@todo build this test
        $this->assertTrue(true, "Can CreatePaymentTransactionID");
    }
    public function testCanCreateIdentityRequest()
    {
        // @todo: move cats part to separate function
        // params:
        
        $catListObject = new BluemIdentityCategoryList();
        $catListObject->addCat("CustomerIDRequest");
        $catListObject->addCat("BirthDateRequest");
        $catListObject->addCat("AgeCheckRequest");
        $catListObject->addCat("NameRequest");
        $catListObject->addCat("AddressRequest");
        $catListObject->addCat("AddressRequest");
        $catListObject->addCat("BirthDateRequest");
        $catListObject->addCat("GenderRequest");
        $catListObject->addCat("TelephoneRequest");
        $catListObject->addCat("EmailRequest");
        $cats = $catListObject->getCats();
        
        // assert amount of cats and type of cats
        $this->assertEquals(count($cats), 8, "Amount of categories is valid");
        
        // assert type of categories
        $this->assertTrue(is_array($cats), "Categories is an array");
        
        $description = "Testing identity request";
        $debtorReference = "1234";
        $callback = "https://127.0.0.1/bluem-woocommerce/idin_shortcode_callback/go_to_cart";

        // To create AND perform a request:
        $request = $this->bluem->CreateIdentityRequest(
            $cats,
            $description,
            $debtorReference,
            "",
            $callback
        );
        // assert request is an object
        $this->assertTrue(is_object($request), "Request is an object");
        // assert request has class "IdentityRequest"
        $this->assertEquals(get_class($request), IdentityBluemRequest::class, "Request has class IdentityBluemRequest");
        
        
    }
    public function testCanIdentityStatus()
    {
        // params:
        //  ($transactionID, $entranceCode)
        $transactionID = "123";
        $entranceCode = "1234";
        try {
            $statusResponse = $this->bluem->IdentityStatus(
                $transactionID,
                $entranceCode
            );
        } catch ( Exception $e ) {
            // @todo: deal with Exception here
        }
        //@todo build this test
        
        
        $this->assertTrue(true, "Can IdentityStatus");
    }
    public function testCanCreateIdentityTransactionID()
    {
        // params:
        //  (String $debtorReference): String

        //@todo build this test
        $this->assertTrue(true, "Can CreateIdentityTransactionID");
    }
    public function testCanCreateEntranceCode()
    {
        // params:
        //  (): String

        //@todo build this test
        $this->assertTrue(true, "Can CreateEntranceCode");
    }
    public function testCanPerformRequest()
    {
        // params:
        //  (BluemRequest $transaction_request)

        //@todo build this test
        $this->assertTrue(true, "Can PerformRequest");
    }
    public function testCanWebhook()
    {
        // params:
        //  ()

        //@todo build this test
        $this->assertTrue(true, "Can Webhook");
    }
    public function testCanGetIdentityRequestTypes()
    {
        // params:
        //  ()

        //@todo build this test
        $this->assertTrue(true, "Can GetIdentityRequestTypes");
    }
    public function testCanCreateIBANNameCheckRequest()
    {
        // params:
        //  (String $iban, String $name, String $debtorReference = "")

        //@todo build this test
        $this->assertTrue(true, "Can CreateIBANNameCheckRequest");
    }
    public function testCanIBANNameCheck()
    {
        // params:
        //  (String $iban, String $name, String $debtorReference="")

        //@todo build this test
        $this->assertTrue(true, "Can IBANNameCheck");
    }
    public function testCanretrieveBICCodesForContext()
    {
        // params:
        //  ($contextName)

        //@todo build this test
        $this->assertTrue(true, "Can retrieveBICCodesForContext");
    }
    public function testCanretrieveBICsForContext()
    {
        // params:
        //  ($contextName)

        //@todo build this test
        $this->assertTrue(true, "Can retrieveBICsForContext");
    }
    public function testCan_retrieveContext()
    {
        // params:
        //  ($context)

        //@todo build this test
        $this->assertTrue(true, "Can _retrieveContext");
    }
    public function testCanVerifyIPIsNetherlands()
    {
        // params:
        //  @todo add parameter list from documentation
        $result = $this->bluem->VerifyIPIsNetherlands();
        //@todo build this test
        $this->assertEquals($result, false,"Can VerifyIPIsNetherlands");
    }
}
