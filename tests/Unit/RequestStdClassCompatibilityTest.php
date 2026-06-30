<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Requests\EmandateStatusBluemRequest;
use Bluem\BluemPHP\Requests\IBANBluemRequest;
use Bluem\BluemPHP\Requests\IdentityBluemRequest;
use Bluem\BluemPHP\Requests\IdentityStatusBluemRequest;
use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Requests\PaymentStatusBluemRequest;
use stdClass;

final class RequestStdClassCompatibilityTest extends BluemTestCase
{
    public function testPaymentRequestSupportsStdClassConfig(): void
    {
        $request = new PaymentBluemRequest(
            $this->getLegacyConfig(),
            'Payment test',
            'ORDER-123',
            12.34,
            '2026-07-01T00:00:00.000Z',
            'EUR',
            'TRANS123',
            'ENTRANCE123'
        );

        self::assertSame('PTX', $request->transaction_code);
        self::assertStringContainsString('token=BLUEM_TEST_ACCESS_TOKEN', $request->HttpRequestURL());
    }

    public function testPaymentStatusRequestSupportsStdClassConfig(): void
    {
        $request = new PaymentStatusBluemRequest($this->getLegacyConfig(), 'TRANS123');

        self::assertSame('PSX', $request->transaction_code);
        self::assertStringContainsString('<PaymentStatusRequest', $request->XmlString());
    }

    public function testIdentityRequestSupportsStdClassConfig(): void
    {
        $request = new IdentityBluemRequest(
            $this->getLegacyConfig(),
            'ENTRANCE123',
            'none',
            ['CustomerIDRequest'],
            'Identity test',
            'DEBTOR123',
            'https://example.test/return'
        );

        self::assertSame('ITX', $request->transaction_code);
        self::assertStringContainsString('<IdentityTransactionRequest', $request->XmlString());
    }

    public function testIdentityStatusRequestSupportsStdClassConfig(): void
    {
        $request = new IdentityStatusBluemRequest(
            $this->getLegacyConfig(),
            'ENTRANCE123',
            'none',
            'TRANS123'
        );

        self::assertSame('ISX', $request->transaction_code);
        self::assertStringContainsString('<IdentityStatusRequest', $request->XmlString());
    }

    public function testIbanRequestSupportsStdClassConfig(): void
    {
        $request = new IBANBluemRequest(
            $this->getLegacyConfig(),
            'ENTRANCE123',
            'NL91ABNA0417164300',
            'Jane Doe',
            'DEBTOR123'
        );

        self::assertSame('INX', $request->transaction_code);
        self::assertStringContainsString('<IBANCheckTransactionRequest', $request->XmlString());
    }

    public function testEmandateStatusRequestSupportsStdClassConfig(): void
    {
        $request = new EmandateStatusBluemRequest(
            $this->getLegacyConfig(),
            'MANDATE123',
            'ENTRANCE123'
        );

        self::assertSame('SRX', $request->transaction_code);
        self::assertStringContainsString('<EMandateStatusRequest', $request->XmlString());
    }

    private function getLegacyConfig(): stdClass
    {
        $config = $this->getConfig();
        $config->accessToken = 'BLUEM_TEST_ACCESS_TOKEN';
        $config->paymentBrandID = 'BLUEM_PAYMENT_BRANDID';
        $config->IDINBrandID = 'BLUEM_IDENTITY_BRANDID';

        return $config;
    }
}
