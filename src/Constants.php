<?php

namespace Bluem\BluemPHP;

readonly class Constants
{
    public const string PRODUCTION_ENVIRONMENT = "prod";
    public const string ACCEPTANCE_ENVIRONMENT = "acc";
    public const string TESTING_ENVIRONMENT = "test";

    /** @var string[]  */
    public const array ENVIRONMENTS = [
        self::PRODUCTION_ENVIRONMENT,
        self::ACCEPTANCE_ENVIRONMENT,
        self::TESTING_ENVIRONMENT,
    ];

    // A fallback merchantID used in testing environments
    public const string BLUEM_STATIC_MERCHANT_ID = "0020000387";


    // Possible return statuses, used by validation
    public const string EXPECTED_RETURN_NONE = "none";
    public const string EXPECTED_RETURN_SUCCESS = "success";
    public const string EXPECTED_RETURN_CANCELLED = "cancelled";
    public const string EXPECTED_RETURN_EXPIRED = "expired";
    public const string EXPECTED_RETURN_FAILURE = "failure";
    public const string EXPECTED_RETURN_OPEN = "open";
    public const string EXPECTED_RETURN_PENDING = "pending";

    public const array POSSIBLE_RETURN_STATUSES = [
        self::EXPECTED_RETURN_NONE,
        self::EXPECTED_RETURN_SUCCESS,
        self::EXPECTED_RETURN_CANCELLED,
        self::EXPECTED_RETURN_EXPIRED,
        self::EXPECTED_RETURN_FAILURE,
        self::EXPECTED_RETURN_OPEN,
        self::EXPECTED_RETURN_PENDING,
    ];
}
