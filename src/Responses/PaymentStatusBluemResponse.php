<?php

namespace Bluem\BluemPHP\Responses;

class PaymentStatusBluemResponse extends StatusBluemResponse {
    public static $transaction_type = "Payment";
    public static $response_primary_key = 'PaymentStatus';
    public static $error_response_type = 'PaymentErrorResponse';
}
