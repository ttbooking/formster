<?php

declare(strict_types=1);

namespace TTBooking\Formster\Enums;

use LogicException;

enum EnumRepresentation
{
    /** Auto-detect based on the number of enumeration cases */
    case Auto;

    /** Dropdown for a single enumeration or multiselect/listbox for enum collection */
    case Select;

    /** Radio buttons for a single enumeration or checkboxes for enum collection */
    case Radio;

    public function component(): string
    {
        return match ($this) {
            self::Auto => throw new LogicException('External auto-detection logic should be used.'),
            self::Select => 'formster::form.select',
            self::Radio => 'formster::form.radio',
        };
    }
}
