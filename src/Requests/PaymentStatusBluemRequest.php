<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;

class PaymentStatusBluemRequest extends BluemRequest
{
    public $request_url_type = "pr";
    public $typeIdentifier = "requestStatus";
    public $transaction_code = "PSX";
    protected $xmlInterfaceName = "EPaymentInterface";

    public function __construct(
        $config,
        $transactionID,
        $expected_return = "",
        $entranceCode = ""
    ) {
        parent::__construct($config, $entranceCode, $expected_return);


        if(method_exists($config,'setBrandID')) {
            if (isset($config->paymentBrandID)
                && $config->paymentBrandID !== ""
            ) {
                $config->setBrandID($config->paymentBrandID);
            } else {
                $config->setBrandID($config->brandID);
            }
        } else if (isset($config->paymentBrandID)
            && $config->paymentBrandID !== ""
        ) {
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
