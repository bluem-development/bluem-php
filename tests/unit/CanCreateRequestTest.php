<?php


// require_once __DIR__ . '/../vendor/autoload.php';

use Bluem\BluemPHP\Bluem;

class BluemGenericTestCase extends \PHPUnit\Framework\TestCase
{
    protected $bluem;

    protected function setUp() : void
    {
        $envfile =__DIR__. '/../..';
        $dotenv = Dotenv\Dotenv::createImmutable($envfile);
        $dotenv->load();


        $bluem_config = new Stdclass();
        $bluem_config->environment = $_ENV['BLUEM_ENV'];
        $bluem_config->senderID = $_ENV['BLUEM_SENDER_ID'];

        $bluem_config->brandID = $_ENV['BLUEM_BRANDID'];
        $bluem_config->test_accessToken = $_ENV['BLUEM_TEST_ACCESS_TOKEN'];
        $bluem_config->IDINBrandID = $_ENV['BLUEM_BRANDID'];
        $bluem_config->merchantID = $_ENV['BLUEM_MERCHANTID'];
        $bluem_config->merchantReturnURLBase =$_ENV['BLUEM_MERCHANTRETURNURLBASE'];
        
        $bluem_config->production_accessToken = "" ;
        $bluem_config->expectedReturnStatus = "success" ;
        $bluem_config->eMandateReason = "eMandateReason" ;
        $bluem_config->localInstrumentCode = "B2B" ;
        // $_ENV['BLUEM_THANKSPAGE'];

        $this->bluem = new Bluem($bluem_config);
    }
}

class CanCreateRequestTest extends BluemGenericTestCase
{
    public function testCanCreateRequest()
    {
        $customer_id = "testcustomer001";
        $order_id = "testorder01231";
        $request = $this->bluem->CreateMandateRequest($customer_id, $order_id, "default");

        try {
            // $this->assertEquals($request->getStatus(), "success");
            $response = $this->bluem->PerformRequest($request);
        } catch (Exception $e) {
            $this->assertTrue(false, "Error; Exception. ".$e->getMessage());
        }

        if (is_a($response, "Bluem\BluemPHP\Responses\ErrorBluemResponse", false)) {
            $this->assertTrue(false, "Error response returned: ".($response->error()));
        } else {
            $this->assertTrue(true, "Can create request and perform it");
        }
    }
}
