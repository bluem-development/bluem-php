<?php

namespace Bluem\BluemPHP\Observability;

final class SimpleMailer {

    public function notifyConfiguration(object $configuration): bool {
        $message = "Here are the key-value pairs from the configuration:\n\n";
        foreach ($configuration as $key => $value) {
            $message .= strtoupper($key) . ": " . $value . "\n";
        }
        $message .= "Environment: ". $_SERVER['SERVER_NAME'] === 'localhost' ? 'development' : 'production';

        // Additional headers
        $headers = "From: sender@example.com\r\n";
        $headers .= "Reply-To: sender@example.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $to = "pluginsupport@bluem.nl";
        $subject = "Library Bluem-php instantiatie";

        // Send the email
        return mail($to, $subject, $message, $headers);
    }
}

