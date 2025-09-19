<?php
namespace WebOffice;

use DateTime;
use IntlDateFormatter;

class DT {
    private string $dt;
    private ?string $tz;

    /**
     * Datetime object
     * @param string $datetime Datetime string
     * @param string|null $timezone Timezone
     */
    public function __construct(string $datetime, ?string $timezone = null) {
        $this->dt = $datetime;
        $this->tz = $timezone;
    }

    /**
     * Formats the time based on the language
     * @param string $language Language code (locale)
     * @return string
     */
    public function format(string $language): string {
        // Create DateTime object
        $dateTime = new DateTime($this->dt);
        // Set timezone if provided
        if ($this->tz !== null) {
            $dateTime->setTimezone(new \DateTimeZone($this->tz));
        }

        // Create IntlDateFormatter with default date and time styles
        $formatter = new IntlDateFormatter(
            $language,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $this->tz ?? date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN
        );
        return $formatter->format($dateTime);
    }
    /**
     * Get Unix timestamp
     * @return int
     */
    public function getTimestamp(): int {
        $dateTime = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dateTime->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dateTime->getTimestamp();
    }
    /**
     * Get date in specified format (default 'Y-m-d')
     * @param string $format Date format
     * @return string
     */
    public function getDate(string $format = 'Y-m-d'): string {
        $dateTime = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dateTime->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dateTime->format($format);
    }

    /**
     * Get time in specified format (default 'H:i:s')
     * @param string $format Time format
     * @return string
     */
    public function getTime(string $format = 'H:i:s'): string {
        $dateTime = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dateTime->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dateTime->format($format);
    }
    /**
     * Get difference in seconds between this datetime and another DT object
     * @param DT $other
     * @return int
     */
    public function diff(DT $other): int {
        $dt1 = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dt1->setTimezone(new \DateTimeZone($this->tz));
        }
        $dt2 = new DateTime($other->dt);
        if ($other->tz !== null) {
            $dt2->setTimezone(new \DateTimeZone($other->tz));
        }
        return $dt1->getTimestamp() - $dt2->getTimestamp();
    }
    /**
     * Check if datetime is in the past
     * @return bool
     */
    public function isPast(): bool {
        $now = new DateTime();
        $dt = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dt->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dt < $now;
    }

    /**
     * Check if datetime is in the future
     * @return bool
     */
    public function isFuture(): bool {
        $now = new DateTime();
        $dt = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dt->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dt > $now;
    }
    /**
     * Check if datetime is in the present
     * @return bool
     */
    public function isPresent(): bool {
        $now = new DateTime();
        $dt = new DateTime($this->dt);
        if ($this->tz !== null) {
            $dt->setTimezone(new \DateTimeZone($this->tz));
        }
        return $dt == $now;
    }
}