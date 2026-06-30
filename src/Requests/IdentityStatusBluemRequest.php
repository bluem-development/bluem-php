<?php

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IdentityContext;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use stdClass;

class IdentityStatusBluemRequest extends BluemRequest
{
    public $request_url_type = "ir";

    public $typeIdentifier = "requestStatus";

    public $transaction_code = "ISX";

    protected $xmlInterfaceName = "IdentityInterface";

    public function __construct(
        BluemConfiguration|stdClass $config,
        $entranceCode,
        $expectedReturn,
        $transactionID
    )
    {
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID when using IDIN
        if ($config instanceof BluemConfiguration && isset($config->IDINBrandID) && $config->IDINBrandID !== "") {
            $config->setBrandId($config->IDINBrandID);
        } elseif ($config instanceof BluemConfiguration) {
            $config->setBrandId($config->brandID);
        } elseif (isset($config->IDINBrandID) && $config->IDINBrandID !== "") {
            $config->brandID = $config->IDINBrandID;
        }

        $this->transactionID = $transactionID;

        $this->context = new IdentityContext();
    }

    // @todo: deprecated, remove

    public function TransactionType(): string
    {
        return "ISX";
    }

    #[\Override]
    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'IdentityStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );
    }
}
