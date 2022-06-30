<?php

namespace Integration;

require_once __DIR__ . '/BluemGenericTest.php';

class IdentityRequestTest extends BluemGenericTest
{
    public function testCanCreateIdentityRequestWithWeirdCharacters() {

        $request = $this->bluem->CreateIdentityRequest(
            ["CustomerIDRequest","NameRequest"],
            "Identificatie EsmeÃ© Timmerman",
            "nope",
            random_int(0,12353),
            "https://google.com"
        );

//        $this->assertInstanceOf(IdentityBluemRequest::class, $request);

        $response = $this->bluem->performRequest($request);

        $this->assertEquals(false,$response->Error());

//        $transactionId = $response->GetTransactionID();
//        $entranceCode = $response->GetEntranceCode();
//        $status = $this->bluem->IdentityStatus(
//            $transactionId,
//            $entranceCode
//        );
//
        $this->assertEquals(false, $response->Error());

    }
}
