<?php
namespace MapasCulturais;

use DateTimeZone;

class DateTime extends \DateTime
{
    static public string $datetime = 'now';

    public function __construct(string $datetime = 'now', DateTimeZone|null $timezone = null)
    {
        if ($datetime == 'now') {
            $datetime = self::$datetime;
        }

        return parent::__construct($datetime, $timezone);
    }

    static function date(string $format, ?int $timestamp = null) {
        $timestamp = $timestamp ?: strtotime(self::$datetime);
        return date($format, $timestamp);
    }
}