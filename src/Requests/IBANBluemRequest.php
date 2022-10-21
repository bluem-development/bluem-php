<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IBANCheckContext;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;

/**
 * IBAN Name Check Request object
 */
class IBANBluemRequest extends BluemRequest implements BluemRequestInterface {
    public $request_url_type = "icr";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "INX";
    private $xmlInterfaceName = "IBANCheckInterface";
    private $_inputIban;
    private $_inputName;
    private $_debtorReference;

    /**
     * Construct the request and prepare all properties
     *
     * @param [type] $config
     * @param [type] $entranceCode
     * @param string $_inputIban
     * @param string $_inputName
     * @param string $_debtorReference
     */
    public function __construct(
        $config,
        $entranceCode,
        string $_inputIban,
        string $_inputName,
        string $_debtorReference = ""
    ) {
        parent::__construct( $config, $entranceCode );

        $this->_inputIban = $this->_sanitizeIban( $_inputIban );
        $this->_inputName = $this->_sanitizeName( $_inputName );

        $this->_debtorReference = $_debtorReference;

        $this->context = new IBANCheckContext();
    }
    // @todo: deprecated, remove

    /**
     * Sanitize input IBAN for proper XML handling
     *
     * @param string $iban Given IBAN to sanitize
     *
     * @return string
     */
    private function _sanitizeIban( string $iban ): string {
        return trim(
            str_replace( ' ', '', $iban )
        );
    }

    /**
     * Sanitize input Name for proper XML handling
     *
     * @param string $name Given name to check
     *
     * @return string
     */
    private function _sanitizeName( string $name ): string {
        return trim( $name );
    }

    /**
     * Retrieve the Bluem Transaction Type Code for this request
     *
     * @return string
     */
    public function TransactionType(): string {
        return "INX";
    }

    /**
     * Generate XML string that is used in the request
     *
     * @return string
     */
    public function XmlString(): string {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IBANCheckTransactionRequest',
                PHP_EOL .
                '<IBAN>' .
                $this->_inputIban .
                '</IBAN>' . PHP_EOL .
                '<AssumedName>' .
                $this->_inputName .
                '</AssumedName>' . PHP_EOL .
                '<DebtorReference>' .
                $this->_debtorReference .
                '</DebtorReference>' . PHP_EOL,
                []
            )
        );
    }

}
