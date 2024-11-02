<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Bluem\BluemPHP\Interfaces\WebhookInterface;
use Bluem\BluemPHP\Validators\WebhookSignatureValidation;
use Bluem\BluemPHP\Validators\WebhookXmlValidation;
use Exception;
use SimpleXMLElement;

class Webhook implements WebhookInterface
{
    private const PAYMENTS_SERVICE = 'Payments';
    private const IDENTITY_SERVICE = 'Identity';
    private const EMANDATES_SERVICE = 'EMandates';
    private const XML_UTF8_CONTENT_TYPE = "text/xml; charset=UTF-8";
    private const STATUSCODE_BAD_REQUEST = 400;

    public string $service = '';

    public ?SimpleXMLElement $xmlObject = null;

    private string $xmlInterface = '';

    private string $xmlPayloadKey = '';

    public function __construct(
        private $senderID,
        private $environment = BLUEM_ENVIRONMENT_TESTING,
        private $webhookData = ''
    ) {
        $this->parse($this->webhookData);
    }

    private function parse($xmlData = ''): void
    {
        if (empty($xmlData))
        {
            if (!$this->isHttpsRequest()) {
                $this->exitWithError('Not HTTPS');
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->exitWithError('Not POST');
                return;
            }

            // Check: content type: XML with utf-8 encoding
            if ($_SERVER["CONTENT_TYPE"] !== self::XML_UTF8_CONTENT_TYPE) {
                $this->exitWithError('Wrong Content-Type given: should be XML with UTF-8 encoding');
                return;
            }

            // Check: An empty POST to the URL (normal HTTP request) always has to respond with HTTP 200 OK.
            $xmlData = file_get_contents('php://input');

            if (empty($xmlData)) {
                $this->exitWithError('No data body given');
                return;
            }
        }

        $xmlObject = $this->parseRawXML($xmlData);

        if (! $xmlObject instanceof \SimpleXMLElement) {
            $this->exitWithError('Could not parse XML');
            return;
        }

        $xmlValidation = (new WebhookXmlValidation($this->senderID))->validate($xmlObject);
        if (! $xmlValidation::$isValid) {
            $this->exitWithError($xmlValidation->errorMessage());
            return;
        }

        $signatureValidation = (new WebhookSignatureValidation($this->environment))->validate($xmlData);
        if (! $signatureValidation::$isValid) {
            $this->exitWithError($signatureValidation->errorMessage());
            return;
        }

        $this->xmlObject = $xmlObject;

        $this->setServiceInterface();
    }

    private function isHttpsRequest(): bool
    {
        return ((!empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] === 443
        );
    }

    private function exitWithError(string $string): void
    {
        http_response_code(self::STATUSCODE_BAD_REQUEST);
    }

    private function parseRawXML($postData): string|SimpleXMLElement
    {
        try {
            $xmlObject = new SimpleXMLElement($postData);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $xmlObject;
    }

    private function setServiceInterface(): void
    {
        $this->xmlInterface = $this->xmlObject->children()[0]->getName();
        switch ($this->xmlInterface) {
            case 'EPaymentInterface':
                $this->xmlPayloadKey = "PaymentStatusUpdate";
                $this->service = self::PAYMENTS_SERVICE;
                break;
            case 'IdentityInterface':
                $this->xmlPayloadKey = "IdentityStatusUpdate";
                $this->service = self::IDENTITY_SERVICE;
                break;
            case 'EMandateInterface':
                $this->xmlPayloadKey = "EMandateStatusUpdate";
                $this->service = self::EMANDATES_SERVICE;
                break;
        }
    }

    private function getPayloadValue(string $key): SimpleXMLElement|string|null
    {
        $payload = $this->getPayload()->$key ?? null;
        if ($payload === null) {
            return null;
        }

        if ((is_countable($payload->children()) ? count($payload->children()) : 0) > 0) {
            return $payload;
        }
        return $payload ?? '';
    }

    private function getPayload(): SimpleXMLElement
    {
        if ($this->isEmandates()) {
            return $this->xmlObject->{$this->xmlInterface}->{$this->xmlPayloadKey}->EMandateStatus;
        }
        return $this->xmlObject->{$this->xmlInterface}->{$this->xmlPayloadKey};
    }

    // @todo: move all attributes to subclasses of webhook (e.g. PaymentsWebhook)
    private function isPayments(): bool
    {
        return $this->service === self::PAYMENTS_SERVICE;
    }

    private function isEmandates(): bool
    {
        return $this->service === self::EMANDATES_SERVICE;
    }

    private function isIdentity(): bool
    {
        return $this->service === self::IDENTITY_SERVICE;
    }


    // general, shared attributes
    public function getEntranceCode(): ?string
    {
        return $this->getPayload()->attributes()->entranceCode . '' ?? null;
    }

    // Different attributes for different services
    public function getDebtorReference(): ?string
    {
        $key = "DebtorReference";
        if ($this->isPayments()) {
            return $this->getPayloadValue($key);
        }
        return $this->getPayload()->$key . "";
    }

    public function getPurchaseID(): ?string
    {
        $key = "PurchaseID";
        if ($this->isPayments()) {
            return $this->getPayloadValue($key);
        }
        if ($this->isEmandates()) {
            return $this->getPayload()->$key . "";
        }

        return '';
    }

    public function getStatus(): ?string
    {
        if ($this->isPayments()) {
            $value = $this->getPayloadValue('Status');

            if (!empty($value)) {
                return $value;
            }
        }
        return $this->getPayload()->Status . "";
    }

    // payments specific
    public function getTransactionID(): ?string
    {
        if ($this->isPayments()) {
            return $this->getPayloadValue('TransactionID');
        }
        // else if identity
        return $this->getPayload()->TransactionID;
    }

    public function getCreationDateTime(): ?string
    {
        if ($this->isPayments()) {
            return $this->getPayloadValue('CreationDateTime');
        }
        return $this->getPayload()->CreationDateTime;
    }

    public function getPaymentReference(): ?string
    {
        return $this->getPayloadValue('PaymentReference');
    }

    public function getAmount(): ?string
    {
        return $this->getPayloadValue('Amount');
    }
    public function getAmountPaid(): ?string
    {
        return $this->getPayloadValue('AmountPaid');
    }
    public function getCurrency(): ?string
    {
        return $this->getPayloadValue('Currency');
    }
    public function getPaymentMethod(): ?string
    {
        return $this->getPayloadValue('PaymentMethod');
    }
    public function getPaymentMethodDetails(): ?SimpleXMLElement
    {
        return $this->getPayloadValue('PaymentMethodDetails');
    }

    public function getIDealDetails(): ?SimpleXMLElement
    {
        $paymentDetails = $this->getPaymentMethodDetails();

        if (!$paymentDetails instanceof \SimpleXMLElement) {
            return null;
        }
        return $paymentDetails->IDealDetails;
    }
    public function getDebtorAccountName(): ?string
    {
        $details = $this->getIDealDetails();
        if (!$details instanceof \SimpleXMLElement) {
            return "";
        }
        return $details->DebtorAccountName."" ?? "";
    }
    public function getDebtorIBAN(): ?string
    {
        $details = $this->getIDealDetails();
        if (!$details instanceof \SimpleXMLElement) {
            return "";
        }
        return $details->DebtorIBAN."" ?? "";
    }
    public function getDebtorBankID(): ?string
    {
        $details = $this->getIDealDetails();
        if (!$details instanceof \SimpleXMLElement) {
            return "";
        }
        return $details->DebtorBankID."" ?? "";
    }


    // MANDATES specific

    public function getMandateID(): ?string
    {
        return $this->getPayload()->MandateID;
    }
    public function getStatusDateTime(): ?string
    {
        return $this->getPayload()->StatusDateTime;
    }
    public function getOriginalReport(): ?string
    {
        return $this->getPayload()->OriginalReport;
    }

    public function getAcceptanceReportArray(): array
    {
        $report = $this->getPayload()->AcceptanceReport;
        return [
            'DateTime' => $report->DateTime . "",
            'ValidationReference' => $report->ValidationReference . "",
            'AcceptedResult' => $report->AcceptedResult . "",
            'MandateRequestID' => $report->MandateRequestID . "",
            'MandateRequestType' => $report->MandateRequestType . "",
            'ServiceLevelCode' => $report->ServiceLevelCode . "",
            'LocalInstrumentCode' => $report->LocalInstrumentCode . "",
            'SequenceType' => $report->SequenceType . "",
            'MandateReason' => $report->MandateReason . "",
            'CreditorID' => $report->CreditorID . "",
            'SchemeName' => $report->SchemeName . "",
            'CreditorName' => $report->CreditorName . "",
            'CreditorCountry' => $report->CreditorCountry . "",
            'CreditorAddressLine1' => $report->CreditorAddressLine1 . "",
            'CreditorAddressLine2' => $report->CreditorAddressLine2 . "",
            'CreditorTradeName' => $report->CreditorTradeName . "",
            'DebtorAccountName' => $report->DebtorAccountName . "",
            'DebtorReference' => $report->DebtorReference . "",
            'DebtorIBAN' => $report->DebtorIBAN . "",
            'DebtorBankID' => $report->DebtorBankID . "",
            'DebtorSignerName' => $report->DebtorSignerName . "",
            'PurchaseID' => $report->PurchaseID . ""
        ];
    }


    // identity specific:

    public function getRequestType(): string
    {
        return $this->getPayload()->RequestType ."";
    }

    public function getAuthenticationAuthorityID(): string
    {
        return $this->getPayload()->AuthenticationAuthorityID.'';
    }

    public function getAuthenticationAuthorityName(): string
    {
        return $this->getPayload()->AuthenticationAuthorityName.'';
    }

    public function getIdentityReportArray(): array
    {
        $report = $this->getPayload()->IdentityReport ?? null;

        if (!$report instanceof \SimpleXMLElement) {
            return [];
        }

        return [
            'DateTime' => $report->DateTime.'',
            'CustomerIDResponse' => $report->CustomerIDResponse.'',
            'NameResponse' => [
                'Initials'=>$report->NameResponse->Initials. '',
                'LegalLastName'=>$report->NameResponse->LegalLastName. '',
                'LegalLastNamePrefix'=>$report->NameResponse->LegalLastNamePrefix. '',
                'PreferredLastName'=>$report->NameResponse->PreferredLastName. '',
                'PreferredLastNamePrefix'=>$report->NameResponse->PreferredLastNamePrefix. '',
                'PartnerLastName'=>$report->NameResponse->PartnerLastName. '',
                'PartnerLastNamePrefix'=>$report->NameResponse->PartnerLastNamePrefix. '',
            ],
            'AddressResponse' => [
                'Street'=>$report->AddressResponse->Street. '',
                'HouseNumber'=>$report->AddressResponse->HouseNumber. '',
                'HouseNumberSuffix'=>$report->AddressResponse->HouseNumberSuffix. '' ?? '',
                'PostalCode'=>$report->AddressResponse->PostalCode. '',
                'City'=>$report->AddressResponse->City. '',
                'CountryCode'=>$report->AddressResponse->CountryCode. '',
            ],
            'BirthDateResponse' => $report->BirthDateResponse . '',
            'GenderResponse' => $report->GenderResponse . '',
            'TelephoneResponse1' => $report->TelephoneResponse1 . '',
            'EmailResponse' => $report->EmailResponse .''
        ];
    }
}
