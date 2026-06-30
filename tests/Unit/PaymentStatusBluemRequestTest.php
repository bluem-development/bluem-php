<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Requests\PaymentStatusBluemRequest;

final class PaymentStatusBluemRequestTest extends BluemTestCase
{
    public function testPaymentStatusRequestUsesLegacyCompatiblePropertyDeclarations(): void
    {
        $config = $this->getConfig();
        $config->accessToken = 'BLUEM_TEST_ACCESS_TOKEN';

        $request = new PaymentStatusBluemRequest($config, 'TRANS123');

        self::assertSame('PSX', $request->transaction_code);
        self::assertSame('pr', $request->request_url_type);
        self::assertSame('requestStatus', $request->typeIdentifier);
    }
}
