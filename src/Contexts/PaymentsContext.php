<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;
use RuntimeException;

class PaymentsContext extends BluemContext
{
    public const PAYMENT_METHOD_IDEAL = 'IDEAL';
    public const PAYMENT_METHOD_PAYPAL = 'PayPal';
    public const PAYMENT_METHOD_CREDITCARD = 'CreditCard';
    public const PAYMENT_METHOD_SOFORT = 'Sofort';
    public const PAYMENT_METHOD_SOFORT_DIGITAL_SERVICES = 'SofortDigitalServices';
    public const PAYMENT_METHOD_CARTE_BANCAIRE = 'CarteBancaire';
    public const PAYMENT_METHOD_BANCONTACT = 'Bancontact';
    public const PAYMENT_METHOD_GIROPAY = 'Giropay';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_IDEAL,
        self::PAYMENT_METHOD_PAYPAL,
        self::PAYMENT_METHOD_CREDITCARD,
        self::PAYMENT_METHOD_SOFORT,
        self::PAYMENT_METHOD_SOFORT_DIGITAL_SERVICES,
        self::PAYMENT_METHOD_CARTE_BANCAIRE,
        self::PAYMENT_METHOD_BANCONTACT,
        self::PAYMENT_METHOD_GIROPAY,
    ];

    public string $debtorWalletElementName = self::PAYMENT_METHOD_IDEAL;

    /**
     * @var array
     */
    private array $paymentMethodDetails;

    /**
     * PaymentsContext constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("BUNQNL2A", "Bunq"),
                new BIC("INGBNL2A", "ING"),
                new BIC("KNABNL2H", "Knab"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "SNS"),
                new BIC("TRIONL2U", "Triodos Bank"),
                new BIC("FVLBNL22", "Van Lanschot"),
                new BIC("REVOLT21", "Revolut"),
                new BIC("BITSNL2A", "Yoursafe"),
                new BIC("NTSBDEB1", "N26"),
                new BIC("NNBANL2G", "Nationale-Nederlanden"),
            ]
        );
    }

    public function getValidationSchema(): string
    {
        return parent::getValidationSchema() . 'EPayment.xsd';
    }

    public function addPaymentMethodDetails(array $details = []): void
    {
        $validationErrors = $this->validateDetails();
        if ($validationErrors !== []) {
            throw new RuntimeException('Invalid details given: '. implode(', ', $validationErrors));
        }

        $this->paymentMethodDetails = $details;
    }

    private function validateDetails(): array
    {
        return [];
        //
        //        if ($this->isIDEAL()) {
        //            // no validation yet
        //        }
        //        if ($this->isPayPal()) {
        //            /**
        //             * For future use.
        //             *
        //             * @todo: validate is valid emailaddress
        //             *
        //             * if(!$details['PayPalAccount']) {
        //             * $errors[] = 'PayPalAccount missing';
        //             * }
        //             *
        //             */
        //        }
        //        if($this->isCreditCard()) {
        //            /**
        //             * For future use.
        //             *
        //             * if(!$details["CardNumber"]) {
        //             * $errors[] = 'CardNumber missing';
        //             * //<xsd:restriction base="xsd:token">x</xsd:restriction>
        //             * }
        //             *
        //             * if(!$details["Name"]) {
        //             * $errors[] = 'Name missing';
        //             * // length 1-32 chars
        //             * }
        //             *
        //             * if(!$details["SecurityCode"]) {
        //             * $errors[] = 'SecurityCode missing';
        //             * //<xsd:pattern value="[0-9]{3,4}"/>  <!-- 3 or 4 digits -->
        //             * }
        //             *
        //             * if(!$details["ExpirationDateMonth"]) {
        //             * $errors[] = 'ExpirationDateMonth missing';
        //             * }
        //             *
        //             * if(!$details["ExpirationDateYear"]) {
        //             * $errors[] = 'ExpirationDateYear missing';
        //             * }
        //             *
        //             */
        //        }
        //        if ($this->isSofort()) {
        //            // add upcoming validation here
        //        }
        //        if ($this->isCarteBancaire()) {
        //            // add upcoming validation here
        //        }
        //
        //        return [];
    }

    public function isIDEAL(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_IDEAL;
    }

    public function isPayPal(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_PAYPAL;
    }

    public function isCreditCard(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_CREDITCARD;
    }

    public function getPaymentDetail(string $key)
    {
        return $this->paymentMethodDetails[$key] ?? null;
    }

    public function isSofort(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_SOFORT;
    }

    public function isCarteBancaire(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_CARTE_BANCAIRE;
    }
}
