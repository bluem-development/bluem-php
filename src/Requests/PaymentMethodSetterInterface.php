<?php

namespace Bluem\BluemPHP\Requests;

interface PaymentMethodSetterInterface
{
    public function setPaymentMethodToIDEAL($BIC = ""): self;
    public function setPaymentMethodToPayPal($payPalAccount = ""): self;
    public function setPaymentMethodToCreditCard(
        string $cardNumber = '',
        string $name = '',
        string $securityCode = '',
        string $expirationDateMonth = '',
        string $expirationDateYear = ''
    ): self;
    public function setPaymentMethodToSofort(): self;
    public function setPaymentMethodToSofortDigitalServices(): self;
    public function setPaymentMethodToCarteBancaire(): self;
    public function setPaymentMethodToBancontact(): self;
    public function setPaymentMethodToGiropay(): self;
}
