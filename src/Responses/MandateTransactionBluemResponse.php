<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class MandateTransactionBluemResponse extends TransactionBluemResponse
{
    public static string $transaction_type = "EMandate";
    public static string $response_primary_key = 'EMandateTransaction';
    public static string $error_response_type = 'EMandateErrorResponse';

    /**
     * Get the mandate ID from the Transaction Response
     */
    public function GetMandateID(): string
    {
        if ($this->EMandateTransactionResponse->MandateID === null ) {
            return false;
        }

        return $this->EMandateTransactionResponse->MandateID . "";
    }

}
