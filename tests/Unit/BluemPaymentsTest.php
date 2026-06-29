<?php

/*
 * © 2026 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Requests\PaymentBluemRequest;

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
}
