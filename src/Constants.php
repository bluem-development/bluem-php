<?php

namespace Bluem\BluemPHP;

readonly class Constants
{
    /**
     * The version of this plug-in, to be updated upon each release
     */
    public const string PHP_PLUGIN_VERSION = '2.5';

    public const string PRODUCTION_ENVIRONMENT = 'prod';
    public const string ACCEPTANCE_ENVIRONMENT = 'acc';
    public const string TESTING_ENVIRONMENT = 'test';

    public const string MANDATES_CONTEXT = 'Mandates';
    public const string PAYMENTS_CONTEXT = 'Payments';
    public const string IDENTITY_CONTEXT = 'Identity';

    /**
     * All available context values
     *
     * @var string[]
     */
    public const array AVAILABLE_CONTEXTS = [
        self::MANDATES_CONTEXT,
        self::PAYMENTS_CONTEXT,
        self::IDENTITY_CONTEXT,
    ];

    /**
     * All available environment values
     *
     * @var string[]
     */
    public const array ENVIRONMENTS = [
        self::PRODUCTION_ENVIRONMENT,
        self::ACCEPTANCE_ENVIRONMENT,
        self::TESTING_ENVIRONMENT,
    ];

    /**
     * A fallback merchantID used in testing environments
     */
    public const string BLUEM_STATIC_MERCHANT_ID = '0020000387';

    /**
     * No specific test return outcome requested.
     */
    public const string EXPECTED_RETURN_NONE = 'none';

    /**
     * Test flow should return a successful transaction.
     */
    public const string EXPECTED_RETURN_SUCCESS = 'success';

    /**
     * Test flow should return a cancelled transaction.
     */
    public const string EXPECTED_RETURN_CANCELLED = 'cancelled';

    /**
     * Test flow should return an expired transaction.
     */
    public const string EXPECTED_RETURN_EXPIRED = 'expired';

    /**
     * Test flow should return a failed transaction.
     */
    public const string EXPECTED_RETURN_FAILURE = 'failure';

    /**
     * Test flow should return an open transaction.
     */
    public const string EXPECTED_RETURN_OPEN = 'open';

    /**
     * Test flow should return a pending transaction.
     */
    public const string EXPECTED_RETURN_PENDING = 'pending';

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
