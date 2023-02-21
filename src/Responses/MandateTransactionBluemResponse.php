<?php

namespace Bluem\BluemPHP\Responses;

class MandateTransactionBluemResponse extends TransactionBluemResponse {
    public static $transaction_type = "EMandate";
    public static $response_primary_key = 'EMandateTransaction';
    public static $error_response_type = 'EMandateErrorResponse';

    /**
     * Get the mandate ID from the Transaction Response
     */
    public function GetMandateID(): string {
        if ( $this->EMandateTransactionResponse->MandateID === null ) {
            return false;
        }

        return $this->EMandateTransactionResponse->MandateID . "";
    }

}
