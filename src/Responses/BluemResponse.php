<?php

/*
 * (c) 2022 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

use Exception;
use SimpleXMLElement;

/**
 * BluemResponse
 */
class BluemResponse extends SimpleXMLElement {
    /**
     * Response Primary Key used to access the XML structure based on the specific type of response
     *
     * @var String
     */
    public static $response_primary_key;

    /** Transaction type used to differentiate the specific type of response
     *
     * @var String
     */
    public static $transaction_type;

    /** Error response type used to differentiate the specific type of response
     *
     * @var String
     */
    public static $error_response_type;

    public function ReceivedResponse(): bool {
        return $this->Status();
    }

    /**
     * Return if the response is a successful one, in boolean
     */
    public function Status(): bool
    {
        return $this->{static::$error_response_type} === null;
    }

    /**
     * Return the error message, if there is one. Else return null
     *
     */
    public function Error(): ?string {
        if ( $this->EMandateErrorResponse !== null ) {
            return $this->EMandateErrorResponse->Error . "";
        }

        return null;
    }

    /**
     * Retrieve the generated EntranceCode enclosed in this response
     *
     * @throws Exception
     */
    public function GetEntranceCode(): string {
        $attrs = $this->{$this->getParentXmlElement()}->attributes();

        if ( ! isset( $attrs['entranceCode'] ) ) {
            throw new Exception( "An error occurred in reading the transaction response: no entrance code found." );
        }

        return $attrs['entranceCode'] . "";
    }

    // overridden in children
    protected function getParentXmlElement(): string {
        return "";
    }

    protected function getChildXmlElement(): string {
        return static::$response_primary_key;
    }
}
