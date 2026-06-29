<?php

/**
 * © 2026 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use stdClass;

class PaymentStatusBluemRequest extends BluemRequest
{
    public string $request_url_type = 'pr';

    public string $typeIdentifier = 'requestStatus';

    public string $transaction_code = 'PSX';

    protected string $xmlInterfaceName = 'EPaymentInterface';

    public function __construct(
        BluemConfiguration|stdClass $config,
        $transactionID,
        $expected_return = '',
        $entranceCode = ''
    ) {
        parent::__construct($config, $entranceCode, $expected_return);

        if ($config instanceof BluemConfiguration) {
            if (isset($config->paymentBrandID) && $config->paymentBrandID !== '') {
                $config->setBrandId($config->paymentBrandID);
            } else {
                $config->setBrandId($config->brandID);
            }
        } elseif ($config instanceof stdClass && isset($config->paymentBrandID) && $config->paymentBrandID !== '') {
            $config->brandID = $config->paymentBrandID;
        }

        $this->transactionID = $transactionID;

        $this->context = new PaymentsContext();
    }

    // @todo: deprecated, remove
    public function TransactionType(): string
    {
        return $this->transaction_code;
    }

    #[\Override]
    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'PaymentStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );
    }
}
