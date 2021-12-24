<?php
namespace Bluem\Tests\Unit;
require_once __DIR__ . '\BluemGenericTest.php';

class BluemMainTest extends BluemGenericTest
{
    public function testCanConstruct()
    {
        // params:
        //  ($_config = null)

        //@todo build this test
        $this->assertTrue(true,"Can construct");
    }
    public function testCanCreateMandateRequest()
    {
        // params:
        //  @todo add parameter list from documentation

        //@todo build this test
        $this->assertTrue(true, "Can create MandateRequest");
    }
    public function testCanMandate()
    {
        // params:
        //  @todo add parameter list from documentation

        //@todo build this test
        $this->assertTrue(true, "Can Mandate");
    }
    public function testCanMandateStatus()
    {
        // params:
        //  ($mandateID, $entranceCode)

        //@todo build this test
        $this->assertTrue(true, "Can MandateStatus");
    }
    public function testCanCreateMandateID()
    {
        // params:
        //  (String $order_id, String $customer_id): String

        //@todo build this test
        $this->assertTrue(true, "Can CreateMandateID");
    }
    public function testCanGetMaximumAmountFromTransactionResponse()
    {
        // params:
        //  ($response)

        //@todo build this test
        $this->assertTrue(true, "Can Get MaximumAmount From TransactionResponse");
    }
    public function testCanCreatePaymentRequest()
    {
        // params:
        //  @todo add parameter list from documentation

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
        // params:
        //  @todo add parameter list from documentation

        //@todo build this test
        $this->assertTrue(true, "Can Create IdentityRequest");
    }
    public function testCanIdentityStatus()
    {
        // params:
        //  ($transactionID, $entranceCode)

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

        //@todo build this test
        $this->assertTrue(true, "Can VerifyIPIsNetherlands");
    }
}
