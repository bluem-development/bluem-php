<?php

/*
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Contexts\MandatesContext;

class MandatesTest extends BluemTestCase
{
    public function testSupportsGreenMandateBicsForCoreOnly(): void
    {
        $coreContext = new MandatesContext('CORE');

        self::assertSame([
            'ABNANL2A', 'ASNBNL21', 'INGBNL2A', 'RABONL2U', 'RBRBNL21',
            'SNSBNL2A', 'TRIONL2U', 'BUNQNL2A', 'KNABNL2H', 'FVLBNL22',
            'REVOLT21', 'NTSBDEB1', 'OTHERBANK',
        ], $coreContext->getBICCodes());
        self::assertSame('Andere bank', $coreContext->getBICs()[12]->issuerName);

        self::assertSame(
            ['ABNANL2A', 'INGBNL2A', 'RABONL2U'],
            (new MandatesContext('B2B'))->getBICCodes()
        );
    }

    public function testCoreMandateRequestAcceptsAndSerializesGreenMandateBics(): void
    {
        $config = $this->getConfig();
        $config->localInstrumentCode = 'CORE';
        $bluem = new Bluem($config);

        foreach (['BUNQNL2A', 'KNABNL2H', 'FVLBNL22', 'REVOLT21', 'NTSBDEB1', 'OTHERBANK'] as $bic) {
            $request = $bluem->CreateMandateRequest('customer-1', 'order-1', 'mandate-1');
            $request->selectDebtorWallet($bic);

            self::assertStringContainsString('<BIC>' . $bic . '</BIC>', $request->XmlString());
        }
    }

    public function testB2BMandateRequestRejectsGreenMandateBics(): void
    {
        $request = $this->bluem->CreateMandateRequest('customer-1', 'order-1', 'mandate-1');

        $this->expectException(\Exception::class);
        $request->selectDebtorWallet('OTHERBANK');
    }
}
