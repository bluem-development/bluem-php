<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\MandatesContext;

/**
 * EMandateStatusRequest
 */
class EmandateStatusBluemRequest extends BluemRequest
{
    public $typeIdentifier = "requestStatus";
    public $request_url_type = "mr";
    public $transaction_code = "SRX";


    public function __construct(
        $config,
        $mandateID,
        $entranceCode = "",
        $expected_return = ""
    ) {
        parent::__construct(
            $config,
            $entranceCode,
            $expected_return
        );

        $this->xmlInterfaceName = "EMandateInterface";
        $this->typeIdentifier = "requestStatus";

        $this->mandateID = $mandateID;

        $this->context = new MandatesContext($config->localInstrumentCode);
    }

    public function TransactionType()
    {
        return "SRX";
    }

    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'EMandateStatusRequest',
                '<MandateID>' . $this->mandateID . '</MandateID>'
            )
        );

        /* // Reference
                return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <EMandateInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="StatusRequest"
            mode="direct" senderID="'.$this->senderID.'" version="1.0" createDateTime="'.$this->createDateTime.'"
            messageCount="1">
            <EMandateStatusRequest entranceCode="'.$this->entranceCode.'">
                <MandateID>'.$this->mandateID.'</MandateID>
            </EMandateStatusRequest>
        </EMandateInterface>';
        */
    }
}
