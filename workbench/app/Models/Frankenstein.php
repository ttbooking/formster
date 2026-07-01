<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TTBooking\Formster\Entities\Aura;
use Workbench\Database\Factories\FrankensteinFactory;

/**
 * @property string $text
 * @property int $integer
 * @property bool $flag
 */
#[Aura]
class Frankenstein extends Model
{
    /** @use HasFactory<FrankensteinFactory> */
    use HasFactory;

    protected $table = 'frankenstein';
}
