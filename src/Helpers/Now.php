<?php

namespace Bluem\BluemPHP\Helpers;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class Now
{
    private const DEFAULT_TIMEZONE = "Europe/Amsterdam";
    private const LOCAL_DATE_FORMAT = "Y-m-d\TH:i:s";

    private DateTimeImmutable $dateTime;

    public function __construct($timezoneString = self::DEFAULT_TIMEZONE)
    {
        try {
            $timezone = new DateTimeZone($timezoneString);
        } catch (Exception) {
            $timezone = new DateTimeZone(self::DEFAULT_TIMEZONE);
        }

        try {
            $this->dateTime = new DateTimeImmutable(datetime: "now", timezone: $timezone);
        } catch (Exception) {
            $this->dateTime = new DateTimeImmutable("now", self::DEFAULT_TIMEZONE);
        }
    }

    public function format(string $format): string
    {
        return $this->dateTime->format($format);
    }

    public function rfc1123(): string
    {
        return $this->dateTime->format("D, d M Y H:i:s \G\M\T");
    }

    public function getCreateDateTimeForRequest(): string
    {
        return $this->dateTime->format(self::LOCAL_DATE_FORMAT) . ".000Z";
    }

    public function tomorrow(): static
    {
        return $this->addDay(1);
    }

    public function addDay(int $days): static
    {
        $this->dateTime = $this->dateTime->add(new DateInterval("P{$days}D"));
        return $this;
    }

    /**
     * @throws Exception
     */
    public function fromDate(string $dateTimeString): static
    {
        $this->dateTime = new DateTimeImmutable(
            datetime: $dateTimeString,
            timezone: new DateTimeZone(self::DEFAULT_TIMEZONE)
        );

        return $this;
    }

    public function fromTimestamp(string $timestamp): static
    {
        $this->dateTime = (new DateTimeImmutable())
            ->setTimestamp($timestamp)
            ->setTimezone(new DateTimeZone(self::DEFAULT_TIMEZONE));

        return $this;
    }
}
