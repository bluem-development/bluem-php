<?php

namespace Bluem\BluemPHP\Responses;

class PaymentTransactionBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "Payment";
    public static $response_primary_key = "Payment" . "Transaction";
    public static $error_response_type = "Payment" . "ErrorResponse";
}
