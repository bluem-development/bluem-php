<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Integration;


class WebhookTest extends BluemGenericTestCase
{
    /**
     * Test webhook for payments.
     *
     * @return void
     */
    public function testCanPerformWebhookPayment()
    {
        $this->markTestSkipped("To be implemented");

        $dirPath = dirname(dirname(__DIR__)) . '/validation/webhooks';

        $fileName = 'webhook_payment.xml';

        $filePath = $dirPath . '/' . $fileName;

        $status = '';

        if (is_file($filePath)) {
            $xmlData = file_get_contents($filePath);

            $webhook = $this->bluem->Webhook($xmlData);

            if ($webhook !== null) {
                if (method_exists($webhook, 'getStatus')) {
                    $status = $webhook->getStatus();
                }
            }
        }
        $this->assertEquals('Success', $status, $fileName . ': Status not success: ' . $status);
    }

    /**
     * Test webhook for mandates.
     *
     * @return void
     */
    public function testCanPerformWebhookMandate()
    {
        $this->markTestSkipped("To be implemented");

        $dirPath = dirname(dirname(__DIR__)) . '/validation/webhooks';

        $fileName = 'webhook_mandate.xml';

        $filePath = $dirPath . '/' . $fileName;

        $status = '';

        if (is_file($filePath)) {
            $xmlData = file_get_contents($filePath);

            $webhook = $this->bluem->Webhook($xmlData);

            if ($webhook !== null) {
                if (method_exists($webhook, 'getStatus')) {
                    $status = $webhook->getStatus();
                }
            }
        }
        $this->assertEquals('Success', $status, $fileName . ': Status not success: ' . $status);
    }

    /**
     * Test webhook for identity.
     *
     * @return void
     */
    public function testCanPerformWebhookIdentity()
    {
        $this->markTestSkipped("To be implemented");

        $dirPath = dirname(dirname(__DIR__)) . '/validation/webhooks';

        $fileName = 'webhook_identity.xml';

        $filePath = $dirPath . '/' . $fileName;

        $status = '';

        if (is_file($filePath)) {
            $xmlData = file_get_contents($filePath);

            $webhook = $this->bluem->Webhook($xmlData);

            if ($webhook !== null) {
                if (method_exists($webhook, 'getStatus')) {
                    $status = $webhook->getStatus();
                }
            }
        }
        $this->assertEquals('Success', $status,  $fileName . ': Status not success: ' . $status);
    }
}
