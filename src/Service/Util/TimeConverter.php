<?php

declare(strict_types=1);

namespace App\Service\Util;


use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * TimeConverter
 *
 * @author  Markus Bierau <markus.bierau@doccheck.com>
 * @license 2022 DocCheck Community GmbH
 */
class TimeConverter
{
    private const DATETIME_SHORTCUT_YEARS = 'y';
    private const DATETIME_SHORTCUT_MONTHS = 'm';
    private const DATETIME_SHORTCUT_WEEKS = 'w';
    private const DATETIME_SHORTCUT_DAYS = 'd';
    private const DATETIME_SHORTCUT_YESTERDAY = 'yesterday';
    private const DATETIME_SHORTCUT_HOURS = 'h';
    private const DATETIME_SHORTCUT_MINUTES = 'i';
    private const DATETIME_SHORTCUT_SECOND = 's';

    private const DATETIME_SHORTCUTS_TO_CHECK = [
        self::DATETIME_SHORTCUT_YEARS,
        self::DATETIME_SHORTCUT_MONTHS,
        self::DATETIME_SHORTCUT_DAYS,
        self::DATETIME_SHORTCUT_HOURS,
        self::DATETIME_SHORTCUT_MINUTES,
        self::DATETIME_SHORTCUT_SECOND,
    ];


    private const BASIC_TRANSLATION_KEY_PREFIX = 'time.touched.ago.';

    public function __construct(private ?DateTimeInterface $referenceDate = null)
    {
        if (is_null($this->referenceDate)) {
            $this->referenceDate = new DateTimeImmutable();
        }
    }

    public function getUserFriendlyDateTime(
        DateTimeInterface $dateTime,
        ?DateTimeInterface $friendlyViewUntilDate = null
    ): ?string {
        if (
            $friendlyViewUntilDate instanceof DateTimeInterface
            && $friendlyViewUntilDate->getTimestamp() >= $dateTime->getTimestamp()
        ) {
            //dd.mm.yyyy hh:mm
            return $dateTime->format('d.m.Y h:i');
        }

        $difference = $this->referenceDate->diff($dateTime);
        $timeValue = 0;
        $timeUnit = null;
        // check if given date is before referenceDate (now)
        if ($difference->invert) {
            $timeUnitNrToCheck = 0;
            while ($timeValue === 0 && $timeUnitNrToCheck < count(self::DATETIME_SHORTCUTS_TO_CHECK)) {
                $timeUnit = self::DATETIME_SHORTCUTS_TO_CHECK[$timeUnitNrToCheck];
                if ($difference->$timeUnit) {
                    $timeValue = $difference->$timeUnit;

                    // special case yesterday
                    if ($timeUnit === self::DATETIME_SHORTCUT_DAYS && $timeValue === 1) {
                        $timeUnit = self::DATETIME_SHORTCUT_YESTERDAY;
                    }

                    // special case weeks
                    if ($timeUnit === self::DATETIME_SHORTCUT_DAYS && $timeValue > 6) {
                        $timeUnit = self::DATETIME_SHORTCUT_WEEKS;
                        $timeValue = floor($timeValue / 7);
                    }

                    // special case only seconds
                    if ($timeUnit === self::DATETIME_SHORTCUT_SECOND) {
                        $timeUnit = self::DATETIME_SHORTCUT_MINUTES;
                        $timeValue = 1;
                    }
                }
                $timeUnitNrToCheck++;
            }
        }

        if ($timeValue === 0 || is_null($timeUnit)) {
            return $dateTime->format('d.m.Y h:i');
        }

        $key = self::BASIC_TRANSLATION_KEY_PREFIX . $timeUnit;

        return $this->getStringVariant($key, $timeValue);
    }

    private function getStringVariant($key, $parameter): string
    {
        $translations = [
            "time.touched.ago.i"         => "vor %s ".($parameter && $parameter > 1 ? 'Minuten' : 'Minute'),
            "time.touched.ago.h"         => "vor %s ".($parameter && $parameter > 1 ? 'Stunden' : 'Stunde'),
            "time.touched.ago.d"         => "vor %s ".($parameter && $parameter > 1 ? 'Tagen' : 'Tag'),
            "time.touched.ago.w"         => "vor %s ".($parameter && $parameter > 1 ? 'Wochen' : 'Woche'),
            "time.touched.ago.m"         => "vor %s ".($parameter && $parameter > 1 ? 'Monaten' : 'Monat'),
            "time.touched.ago.y"         => "vor %s ".($parameter && $parameter > 1 ? 'Jahren' : 'Jahr'),
            "time.touched.ago.yesterday" => "gestern",
        ];

        if (array_key_exists($key, $translations)) {
            if ($parameter) {
                return sprintf($translations[$key], $parameter);
            } else {
                return $translations[$key];
            }
        }
    }
}
