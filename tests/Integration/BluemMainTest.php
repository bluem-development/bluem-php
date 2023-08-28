<?php
namespace Integration;

use Bluem\BluemPHP\Helpers\BluemIdentityCategoryList;
use Bluem\BluemPHP\Helpers\BluemMaxAmount;
use Bluem\BluemPHP\Requests\EmandateBluemRequest;
use Bluem\BluemPHP\Requests\IdentityBluemRequest;
use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;
use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;
use Bluem\BluemPHP\Responses\MandateTransactionBluemResponse;
use Exception;

require_once 'BluemGenericTestCase.php';

class BluemMainTest extends BluemGenericTestCase
{

    public function testCanCreateMandateRequest(): void
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
        } catch (Exception $e) {
            $this->fail("Could not create mandate request: " . $e->getMessage());
        }
    }
    public function testCanMandate(): void
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
        } catch (Exception $e) {
            $this->fail("Could not create mandate: " . $e->getMessage());
        }

        $this->assertInstanceOf(
            MandateTransactionBluemResponse::class,
            $response
        );
    }
    public function testCanMandateStatus(): void
    {
        // params:
        //  ($mandate_id, $entranceCode)
        $mandate_id    = "12134";
        $entranceCode = "121341231";

        try {
            $response = $this->bluem->MandateStatus($mandate_id, $entranceCode);
        } catch (Exception $e) {
            $this->fail("Could not get mandate status: " . $e->getMessage());
        }

        // @todo: deal with the corresponding status if proper or improper status request
        if ($response->Status()) {

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
    public function testCanCreateMandateID(): void
    {
        // params:
        //  (String $order_id, String $customer_id): String
            $order_id = "1";
            $customer_id = "1";

        try {
            $response = $this->bluem->CreateMandateID($order_id, $customer_id);
        } catch (Exception $e) {
            $this->fail("Could not create mandate ID: " . $e->getMessage());
        }

        // assert response is string

        $this->assertIsString(
            $response,
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
    public function testCanGetMaximumAmountFromStatusResponse(): void
    {
        $this->markTestSkipped('To be fixed');

        // params:
        $customer_id = "1";
        $order_id = "1";
        $mandate_id = "testB2BRecurringCancel";
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
            false,
            $status_response->error(),
            "Mandate Status has an error!"
        );

        $this->assertEquals(
            250.00,
            $status_response->GetMaximumAmount()->amount,
            "Maximum amount is not equal to expected value '250.00'"
        );

        $maxAmount = $status_response->GetMaximumAmount();

        // assert max amount is an object
        $this->assertInstanceOf(
            BluemMaxAmount::class,
            $maxAmount
        );

        $this->assertIsNumeric($maxAmount->amount, "Max amount is not numeric");

        $this->assertEquals(
            "EUR",
            $maxAmount->currency,
            "Max amount currency is not EUR"
        );
    }
    public function testCanCreatePaymentRequest(): void
    {
        $description = "Test payment";
        $debtorReference = "12134";
        $amount = 12.32;
        $dueDateTime = strtotime("+1 day");
        $currency = "EUR";
        $entranceCode = "121341231";
        $debtorReturnURL = "https://www.google.com";

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
    public function testCanPayment(): void
    {
        // params:
        //  @todo add parameter list from documentation

        //@todo build this test
        $this->assertTrue(true, "Can Payment");
    }
    public function testCanPaymentStatus(): void
    {
        // params:
        //  ($transactionID, $entranceCode)

        //@todo build this test
        $this->assertTrue(true, "Can PaymentStatus");
    }
    public function testCanCreatePaymentTransactionID(): void
    {
        // params:
        //  (String $debtorReference): String

        //@todo build this test
        $this->assertTrue(true, "Can CreatePaymentTransactionID");
    }
    public function testCanCreateIdentityRequest(): void
    {
        // @todo: move cats part to separate function
        // params:

        $categoryList = new BluemIdentityCategoryList();
        $categoryList->Add("CustomerIDRequest");
        $categoryList->Add("BirthDateRequest");
        $categoryList->Add("AgeCheckRequest");
        $categoryList->Add("NameRequest");
        $categoryList->Add("AddressRequest");
        $categoryList->Add("AddressRequest");
        $categoryList->Add("BirthDateRequest");
        $categoryList->Add("GenderRequest");
        $categoryList->Add("TelephoneRequest");
        $categoryList->Add("EmailRequest");
        $cats = $categoryList->getCategories();

        // assert amount of cats and type of cats
        $this->assertCount(8, $cats, "Amount of categories is valid");

        // assert type of categories
        $this->assertIsArray($cats, "Categories is an array");

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
        $this->assertIsObject($request, "Request is an object");
        // assert request has class "IdentityRequest"
        $this->assertEquals(IdentityBluemRequest::class, get_class($request), "Request has class IdentityBluemRequest");
    }
    public function testCanIdentityStatus(): void
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
        } catch (Exception) {
            // @todo: deal with Exception here
        }
        //@todo build this test


        $this->assertTrue(true, "Can IdentityStatus");
    }
    public function testCanCreateIdentityTransactionID(): void
    {
        // params:
        //  (String $debtorReference): String

        //@todo build this test
        $this->assertTrue(true, "Can CreateIdentityTransactionID");
    }
}
