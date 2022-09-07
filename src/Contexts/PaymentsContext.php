<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;
use RuntimeException;

class PaymentsContext extends BluemContext {

    public const PAYMENT_METHOD_IDEAL = 'IDEAL';
    public const PAYMENT_METHOD_PAYPAL = 'PayPal';
    public const PAYMENT_METHOD_CREDITCARD = 'CreditCard';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_IDEAL,
        self::PAYMENT_METHOD_PAYPAL,
        self::PAYMENT_METHOD_CREDITCARD
    ];

    public string $debtorWalletElementName = self::PAYMENT_METHOD_IDEAL;

    private array $paymentMethodDetails;

    /**
     * PaymentsContext constructor.
     */
    public function __construct() {
        parent::__construct(
            [
                new BIC( "ABNANL2A", "ABN AMRO" ),
                new BIC( "ASNBNL21", "ASN Bank" ),
                new BIC( "BUNQNL2A", "bunq" ),
                new BIC( "HANDNL2A", "Handelsbanken" ),
                new BIC( "INGBNL2A", "ING" ),
                new BIC( "KNABNL2H", "Knab" ),
                new BIC( "MOYONL21", "Moneyou" ),
                new BIC( "RABONL2U", "Rabobank" ),
                new BIC( "RBRBNL21", "RegioBank" ),
                new BIC( "SNSBNL2A", "SNS" ),
                new BIC( "TRIONL2U", "Triodos Bank" ),
                new BIC( "FVLBNL22", "Van Lanschot" ),
                new BIC( "REVOLT21", "Revolut" ),
            ]
        );
    }

    public function getValidationSchema(): string {
        return parent::getValidationSchema() . 'EPayment.xsd';
    }

    public function addPaymentMethodDetails(array $details = [])
    {
        $validationErrors = $this->validateDetails($details);
        if(count($validationErrors) > 0 ) {
            throw new RuntimeException('Invalid details given: '. implode(', ', $validationErrors));
        }
        $this->paymentMethodDetails = $details;
    }

    private function validateDetails(array $details = []): array
    {
        $errors = [];

        if($this->isIDEAL()) {
            //
        }
        if($this->isPayPal()) {
            /**
             * For future use.
             *
             * @todo: validate is valid emailaddress
             *
             * if(!$details['PayPalAccount']) {
             * $errors[] = 'PayPalAccount missing';
             * }
             *
             */
        }
        if($this->isCreditCard()) {
            /**
             * For future use.
             *
             * if(!$details["CardNumber"]) {
             * $errors[] = 'CardNumber missing';
             * //<xsd:restriction base="xsd:token">x</xsd:restriction>
             * }
             *
             * if(!$details["Name"]) {
             * $errors[] = 'Name missing';
             * // length 1-32 chars
             * }
             *
             * if(!$details["SecurityCode"]) {
             * $errors[] = 'SecurityCode missing';
             * //<xsd:pattern value="[0-9]{3,4}"/>  <!-- 3 or 4 digits -->
             * }
             *
             * if(!$details["ExpirationDateMonth"]) {
             * $errors[] = 'ExpirationDateMonth missing';
             * }
             *
             * if(!$details["ExpirationDateYear"]) {
             * $errors[] = 'ExpirationDateYear missing';
             * }
             *
             */
        }

        return $errors;
    }

    public function isIDEAL()
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_IDEAL;
    }

    public function isPayPal()
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_PAYPAL;
    }

    public function isCreditCard()
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_CREDITCARD;
    }

    public function getPaymentDetail(string $key)
    {
        return $this->paymentMethodDetails[$key] ?? '';
    }
}
