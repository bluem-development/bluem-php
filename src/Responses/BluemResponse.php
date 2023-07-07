<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

use Bluem\BluemPHP\Interfaces\BluemResponseInterface;
use Exception;
use RuntimeException;
use SimpleXMLElement;

/**
 * BluemResponse
 */
class BluemResponse extends SimpleXMLElement implements BluemResponseInterface
{

    public static string $response_primary_key;
    public static string $transaction_type;
    public static ?string $error_response_type = null;

    public function ReceivedResponse(): bool
    {
        return $this->Status();
    }

    /**
     * Return if the response is a successful one, in boolean
     */
    public function Status(): bool
    {
        return self::$error_response_type === null;
    }

    /**
     * Return the error message, if there is one. Else return null
     */
    public function Error(): string
    {
        if ($this->EMandateErrorResponse !== null) {
            return $this->EMandateErrorResponse->Error . "";
        }

        return '';
    }

    /**
     * Retrieve the generated EntranceCode enclosed in this response
     *
     * @throws Exception
     */
    public function GetEntranceCode(): string
    {
        $attrs = $this->{$this->getParentXmlElement()}->attributes();

        if (! $attrs || ! isset($attrs['entranceCode'])) {
            throw new RuntimeException("An error occurred in reading the transaction response: no entrance code found.");
        }

        return $attrs['entranceCode'] . "";
    }

    // overridden in children
    protected function getParentXmlElement(): string
    {
        return '';
    }

    protected function getChildXmlElement(): string
    {
        return self::$response_primary_key;
    }

    protected function getParentStringVariable(string $variable) : string
    {
        return ( isset($this->{$this->getParentXmlElement()}->$variable) ) ? $this->{$this->getParentXmlElement()}->$variable . '' : '';
    }

    protected function getParentElement(): ?SimpleXMLElement
    {
        return $this->{$this->getParentXmlElement()} ?? null;
    }
}
