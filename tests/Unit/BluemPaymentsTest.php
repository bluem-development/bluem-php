<?php

/*
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Contexts\PaymentsContext;

class BluemPaymentsTest extends BluemTestCase
{
    public function testCanCreatePaymentRequest(): void
    {
        $request = $this->bluem->CreatePaymentRequest(
            description: 'Payment test',
            debtorReference: 'order123',
            amount: 12.34,
            currency: 'EUR',
            debtorReturnURL: 'https://example.test/return'
        );

        $this->assertInstanceOf(PaymentBluemRequest::class, $request);
    }

    public function testCreditCardRequestIncludesSecurityCodeWhenProvided(): void
    {
        $request = $this->bluem->CreatePaymentRequest(
            description: 'Credit card payment',
            debtorReference: 'order123',
            amount: 12.34,
            currency: 'EUR',
            debtorReturnURL: 'https://example.test/return'
        );

        $request->setPaymentMethodToCreditCard(securityCode: '123');

        self::assertStringContainsString('<SecurityCode>123</SecurityCode>', $request->XmlString());
    }

    public function testSupportsAllDocumentedIdealIssuingBankBics(): void
    {
        $expectedBics = [
            'ABNANL2A', 'ADYBNL2A', 'ASNBNL21', 'BUNQNL2A', 'BUUTNL2A',
            'INGBNL2A', 'KNABNL2H', 'RABONL2U', 'RBRBNL21', 'SNSBNL2A',
            'TRIONL2U', 'FVLBNL22', 'REVOLT21', 'BITSNL2A', 'NTSBDEB1',
            'NNBANL2G',
        ];

        self::assertSame($expectedBics, (new PaymentsContext())->getBICCodes());
    }
}
