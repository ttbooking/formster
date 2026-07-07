<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Entities\AuraProperty;
use Workbench\Database\Factories\FrankensteinFactory;

/**
 * @property string $text Some text
 * @property int $number Some number
 * @property bool $flag Some flag
 */
#[Aura(properties: [
    'text' => new AuraProperty(validationRules: ['...', 'min:3']),
])]
class Frankenstein extends Model
{
    /** @use HasFactory<FrankensteinFactory> */
    use HasFactory;

    protected $table = 'frankenstein';
}
