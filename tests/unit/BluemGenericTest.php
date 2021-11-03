<?php

use Bluem\BluemPHP\Bluem;

/**
 * Abstract base class for all BluemPHP unit tests.
 */
abstract class BluemGenericTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The Bluem integration object
     *
     * @var Bluem\BluemPHP\Bluem
     */
    protected $bluem;

    /**
     * Set up the required config and objects necessary for proper testing
     *
     * @return void
     */
    protected function setUp() : void
    {
        $envfile =__DIR__. '/../..';
        $dotenv = Dotenv\Dotenv::createImmutable($envfile);
        $dotenv->load();
        
        // Create a Bluem object and set the Bluem configuration details based on your .env file.
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

        $this->bluem = new Bluem($bluem_config);
    }


    /**
     * Perform assertions based on a created BluemPHP Request object
     *
     * @param BluemRequest $request
     * @return void
     */
    protected function _finalizeBluemRequestAssertion($request) :void
    {

        try {
            // $this->assertEquals($request->getStatus(), "success");
            $response = $this->bluem->PerformRequest($request);
        } catch (Exception $e) {
            $this->assertTrue(
                false,
                "Exception when performing the request: ".
                    $e->getMessage()
            );
        }
    
        if (is_a($response, "Bluem\BluemPHP\Responses\ErrorBluemResponse", false)) {
            $this->assertTrue(
                false,
                "Errorenous response returned: ".
                    $response->error()
            );
        } else {
            $cname = get_class($request);
            $this->assertTrue(true, "Can utilize {$cname} request and perform it");
        }
    }
    
}
