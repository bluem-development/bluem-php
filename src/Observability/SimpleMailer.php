<?php

namespace Bluem\BluemPHP\Observability;

final class SimpleMailer {

    public function notifyConfiguration(object $configuration): bool {
        $message = "Here are the key-value pairs from the configuration:\n\n";
        foreach ($configuration as $key => $value) {
            $message .= strtoupper($key) . ": " . $value . "\n";
        }
        $message .= "Environment: ". $this->getEnvironment();
        $message.= 'php_version'.PHP_VERSION;
        $message .= "Bluem PHP library version: ".BLUEM_PHP_LIBRARY_VERSION;

        $adminEmail = "pluginsupport@bluem.nl";

        // Additional headers
        $headers = "From: $adminEmail\r\n";
        $headers .= "Reply-To: $adminEmail\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $to = $adminEmail;
        $subject = "Library Bluem-php instantiatie";

        // Send the email
        return mail($to, $subject, $message, $headers);
    }

    private function getEnvironment()
    {
        return (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] === 'localhost' ? 'development' : 'production');
    }
}

