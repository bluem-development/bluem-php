<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;

class PaymentStatusBluemRequest extends BluemRequest implements BluemRequestInterface {
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
        parent::__construct( $config, $entranceCode, $expected_return );

        if ( isset( $config->paymentBrandID )
             && $config->paymentBrandID !== ""
        ) {
            $config->setBrandID( $config->paymentBrandID );
        } else {
            $config->setBrandID( $config->brandID );
        }

        $this->transactionID = $transactionID;

        $this->context = new PaymentsContext();
    }

    // @todo: deprecated, remove
    public function TransactionType(): string {
        return $this->transaction_code;
    }

    public function XmlString(): string {
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
