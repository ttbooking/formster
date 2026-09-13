<?php

declare(strict_types=1);

namespace TTBooking\Formster\Enums;

enum TemporalComponent
{
    case Date;
    case Time;
    case Both;

    public function component(): string
    {
        return match ($this) {
            self::Date => 'formster::form.date',
            self::Time => 'formster::form.time',
            self::Both => 'formster::form.datetime',
        };
    }

    public function dateFormat(): string
    {
        return match ($this) {
            self::Date => 'Y-m-d',
            self::Time => 'H:i',
            self::Both => 'Y-m-d\TH:i',
        };
    }
}
