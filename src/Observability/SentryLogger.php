<?php

namespace Bluem\BluemPHP\Observability;

use Sentry\State\Scope;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use function Sentry\init as SentryInit;

final class SentryLogger
{
    private const KEY = 'ce6a8fc06ff29a03f805eae2041fdd4e@o4506286009548800';
    private const PROJECT_ID = 4506286012891136;

    public function initialize(object $config): void
    {
        SentryInit([
            'dsn' => 'https://'.self::KEY.'.ingest.sentry.io/'.self::PROJECT_ID,
            'environment' => $this->getEnvironment(),
            'attach_stacktrace'=> true,
            'release' => 'bluem-php@'.BLUEM_PHP_LIBRARY_VERSION,
        ]);

        configureScope(function (Scope $scope) use ($config): void {
            $scope->setContext('config', [
                'phpVersion' => PHP_VERSION,
                'senderID' => $config->senderID,
            ]);
            $scope->setUser(['senderId' => $config->senderID]);
            $scope->setTag('bluemSenderId', $config->senderID);
        });
    }

    private function getEnvironment(): string
    {
        if ($_SERVER['SERVER_NAME'] === 'localhost') {
            return 'development';
        }

        return 'production';
    }

    public function captureMessage(string $message): void
    {
        captureMessage($message);
    }
}
