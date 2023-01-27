<?php

namespace Bluem\BluemPHP\Responses;

class PaymentTransactionBluemResponse extends TransactionBluemResponse {
    public static $transaction_type = "Payment";
    public static $response_primary_key = 'PaymentTransaction';
    public static $error_response_type = 'PaymentErrorResponse';
}
