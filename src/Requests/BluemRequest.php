<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Constants;
use Bluem\BluemPHP\Exceptions\InvalidBluemRequestException;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use Bluem\BluemPHP\Helpers\Now;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Exception;
use SimpleXMLElement;
use stdClass;

abstract class BluemRequest implements BluemRequestInterface
{
    public const int AGRICULTURE = 1;
    public const int CONSTRUCTION = 2;
    public const int HEALTHCARE = 3;

    protected const array TYPE_IDENTIFIERS = [
        'createTransaction',
        'requestStatus',
    ];

    protected string $request_url_type = '';

    protected string $typeIdentifier = '';

    protected string $transaction_code = '';

    protected string $entranceCode = '';

    protected string $requestXML = '';

    protected string $expectedReturn = '';

    protected string $transactionID = '';

    protected string $accessToken = '';

    protected string $brandID = '';

    protected string $merchantID = '';

    protected string $environment = '';

    protected string $requestMode = '';

    protected string $currency = '';

    protected string $merchantReturnURLBase = '';

    protected string $merchantReturnURL = '';

    protected string $defaultReturnURL = '';

    protected string $language = '';

    protected string $paymentBrandID = '';

    protected object $context;

    protected string $requestObjectName = '';

    protected string $xmlInterfaceName = '';

    /**
     * BluemRequest constructor.
     *
     * @param BluemConfiguration|object $config
     *
     * @throws InvalidBluemRequestException
     */
    public function __construct(
        BluemConfiguration|stdClass $config,
        string $entranceCode = '',
        string $expectedReturn = ''
    ) {
        if (! in_array($this->typeIdentifier, self::TYPE_IDENTIFIERS, true)) {
            throw new InvalidBluemRequestException('Invalid transaction type called for', 1);
        }

        $this->environment = $config->environment;
        $this->currency = $config->currency;
        $this->accessToken = $config->accessToken;
        $this->brandID = $config->brandID;
        $this->merchantID = $config->merchantID;
        $this->merchantReturnURLBase = $config->merchantReturnURLBase;
        $this->merchantReturnURL = $config->merchantReturnURL;
        $this->defaultReturnURL = $config->defaultReturnURL;
        $this->language = $config->language;
        $this->requestMode = $config->requestMode;
        $this->expectedReturn = $expectedReturn;
        $this->entranceCode = $entranceCode;
        $this->paymentBrandID = $config->paymentBrandID ?? '';
    }

    public function GenerateEntranceCode(): string
    {
        $entranceCode = (new Now())->format('YmdHisv');

        $prefix = '';
        if ($this->environment === Constants::TESTING_ENVIRONMENT) {
            switch ($this->expectedReturn) {
                case 'success':
                    $prefix = 'HIO100OIH';
                    break;
                case 'cancelled':
                    $prefix = 'HIO200OIH';
                    break;
                case 'expired':
                    $prefix = 'HIO300OIH';
                    break;
                case 'failure':
                    $prefix = 'HIO500OIH';
                    break;
                case 'open':
                    $prefix = 'HIO400OIH';
                    break;
                case 'pending':
                    $prefix = 'HIO600OIH';
                    break;
                case '':
                case 'none':
                default:
                    break;
            }
        }

        return $prefix . $entranceCode;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Retrieve the final XML object based on the constructed XML String
     *
     * @return SimpleXMLElement final XML object
     * @throws Exception
     */
    public function Xml(): SimpleXMLElement
    {
        return new SimpleXMLElement($this->XmlString());
    }

    /**
     * Returning the current XML string; as this is an abstract request, it will
     * be overridden by classes that implement this.
     */
    public function XmlString(): string
    {
        return '';
    }

    /**
     * Crafts the relevant HTTP request url.
     *
     * @return string The http request url.
     */
    public function HttpRequestURL(): string
    {
        $requestUrl = 'https://';

        match ($this->environment) {
            Constants::PRODUCTION_ENVIRONMENT => $requestUrl .= '',
            Constants::ACCEPTANCE_ENVIRONMENT => $requestUrl .= 'acc.',
            default => $requestUrl .= 'test.',
        };

        $requestUrl .= sprintf('viamijnbank.net/%s/', $this->request_url_type);
        $requestUrl .= match ($this->typeIdentifier) {
            'createTransaction' => 'createTransactionWithToken',
            'requestStatus' => 'requestTransactionStatusWithToken',
            default => '?token=' . $this->accessToken,
        };

        return $requestUrl . ('?token=' . $this->accessToken);
    }

    public function retrieveBICObjects(): array
    {
        return $this->context->BICs();
    }

    public function setRequestXML(string $requestXML): void
    {
        $this->requestXML = $requestXML;
    }

    protected function XmlRequestInterfaceWrap(string $interfaceName, string $requestType, string $requestObject): string
    {
        return '<' . $interfaceName . ' ' . $this->XmlRequestInterfaceAttributes($requestType) . '>' . $requestObject . '</' . $interfaceName . '>';
    }

    protected function XmlRequestObjectWrap(string $requestObjectName, string $requestData): string
    {
        return '<' . $requestObjectName . '>' . $requestData . '</' . $requestObjectName . '>';
    }

    protected function XmlRequestInterfaceAttributes(string $requestType): string
    {
        $attributes = [
            'createDateTime' => date('c'),
            'version' => '1.0',
            'type' => $requestType,
            'mode' => $this->requestMode,
            'senderID' => $this->brandID,
            'brandID' => $this->brandID,
            'merchantID' => $this->merchantID,
            'language' => $this->language,
            'merchantReturnURLBase' => $this->merchantReturnURLBase,
            'merchantReturnURL' => $this->merchantReturnURL,
            'defaultReturnURL' => $this->defaultReturnURL,
        ];

        return $this->implodeAttributes($attributes);
    }

    private function implodeAttributes(array $attributes): string
    {
        $output = '';
        foreach ($attributes as $name => $value) {
            if ($value !== '') {
                $output .= ' ' . $name . '="' . $value . '"';
            }
        }

        return $output;
    }

    protected function buildXmlRequest(string $requestType, string $requestObject): string
    {
        return $this->XmlRequestInterfaceWrap($this->xmlInterfaceName, $requestType, $requestObject);
    }

    protected function getParentXmlElement(): string
    {
        return $this->xmlInterfaceName;
    }

    protected function getChildXmlElement(): string
    {
        return $this->requestObjectName;
    }

    protected function validateBaseRequest(): void
    {
        if ($this->entranceCode === '') {
            $this->entranceCode = $this->GenerateEntranceCode();
        }

        if ($this->expectedReturn === '') {
            $this->expectedReturn = Constants::EXPECTED_RETURN_SUCCESS;
        }

        if ($this->requestMode === '') {
            $this->requestMode = 'direct';
        }
    }

    protected function validateRequest(): void
    {
        $this->validateBaseRequest();
    }

    public function setRequestObjectName(string $requestObjectName): void
    {
        $this->requestObjectName = $requestObjectName;
    }

    public function getRequestObjectName(): string
    {
        return $this->requestObjectName;
    }

    public function __toString(): string
    {
        return $this->XmlString();
    }
}
