<?php

namespace Bluem\BluemPHP\Helpers;

use DateTimeImmutable;

class Now
{
    private const TIMEZONE = "Europe/Amsterdam";
    private DateTimeImmutable $dateTime;
    public function __construct() {
        $this->dateTime = new DateTimeImmutable(self::TIMEZONE);
    }

    public function format(string $format): string
    {
        return $this->dateTime->format($format);
    }
}
