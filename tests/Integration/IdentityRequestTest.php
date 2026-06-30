<?php

/*
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Integration;

class IdentityRequestTest extends BluemGenericTestCase
{
    public function testCanCreateIdentityRequestWithWeirdCharacters()
    {

        $request = $this->bluem->CreateIdentityRequest(
            ["CustomerIDRequest", "NameRequest"],
            "Identificatie EsmeÃ© Timmerman",
            "nope",
            random_int(0, 12353),
            "https://google.com"
        );

        $response = $this->bluem->performRequest($request);

        $this->assertEquals(false, $response->Error());
    }
}
