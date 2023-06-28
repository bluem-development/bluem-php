<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class PaymentStatusBluemResponse extends StatusBluemResponse {
    public static string $transaction_type = "Payment";
    public static string $response_primary_key = 'PaymentStatus';
    public static string $error_response_type = 'PaymentErrorResponse';
}
