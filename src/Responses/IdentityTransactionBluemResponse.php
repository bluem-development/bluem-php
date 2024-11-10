<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class IdentityTransactionBluemResponse extends TransactionBluemResponse
{
    public static string $transaction_type = "Identity";
    public static string $response_primary_key = 'IdentityTransaction';
    public static ?string $error_response_type = 'IdentityErrorResponse';
}
