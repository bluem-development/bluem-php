<?php

namespace Bluem\BluemPHP\Responses;

class MandateTransactionBluemResponse extends TransactionBluemResponse {
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Transaction";
    public static $error_response_type = "EMandate" . "ErrorResponse";

    /**
     * Get the mandate ID from the Transaction Response
     * @return string
     */
    public function GetMandateID(): string {
        if ( ! isset( $this->EMandateTransactionResponse->MandateID ) ) {
            return false;
        }

        return $this->EMandateTransactionResponse->MandateID . "";
    }

}
