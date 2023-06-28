<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use SimpleXMLElement;

// @todo: add XML validator tests
// @todo: use XSD validation with given XSDs

class WebhookXmlValidation extends WebhookValidator
{
    private const ALLOWED_SERVICE_INTERFACES = [
        'EPaymentInterface', 'IdentityInterface', 'EMandateInterface'
    ];

    public function __construct(private string $senderID)
    {
    }

    public function validate(SimpleXMLElement $data): self
    {
        $serviceInterface = $data->children()[0];
        if (!in_array($serviceInterface->getName(), self::ALLOWED_SERVICE_INTERFACES)) {
            $this->addError("Invalid service interface name: " . $serviceInterface->getName());
        }

        $givenSenderID = ((string)$serviceInterface->attributes()['senderID']);
        if ($this->senderID !== $givenSenderID) {
            $this->addError("Invalid senderID");
        }
        if ((string)$serviceInterface->attributes()['type'] !== "StatusUpdate") {
            $this->addError("Invalid service interface type attribute");
        }

        if ((int)$serviceInterface->attributes()['messageCount'] !== 1) {
            $this->addError("Invalid service interface messageCount attribute");
        }

        if ( $data->Signature->SignatureValue === null ) {
            $this->addError("Invalid Signature Value");
        }

        if ( $data->Signature->KeyInfo->KeyName === null ) {
            $this->addError("Invalid KeyName");
        }

        return $this;
    }
}
