<?php

declare(strict_types=1);

namespace TTBooking\Formster\Enums;

enum TemporalComponent
{
    case Date;
    case Time;
    case Both;
}
