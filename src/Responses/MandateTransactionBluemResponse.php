<?php

namespace Bluem\BluemPHP\Responses;

class MandateTransactionBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Transaction";
    public static $error_response_type = "EMandate" . "ErrorResponse";

    // @todo Function to retrieve Mandate ID?
}
