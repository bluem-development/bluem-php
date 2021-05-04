<?php

namespace Bluem\BluemPHP\Responses;

class PaymentStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "Payment";
    public static $response_primary_key = "Payment" . "Status";
    public static $error_response_type = "Payment" . "ErrorResponse";
}
