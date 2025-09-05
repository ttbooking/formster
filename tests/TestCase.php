<?php

declare(strict_types=1);

namespace TTBooking\Formster\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Facades\ActionHandler;
use TTBooking\Formster\Facades\PropertyHandler;
use TTBooking\Formster\Facades\PropertyParser;

abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;

    public Aura $aura;

    protected function getPackageAliases($app): array
    {
        return [
            'PropertyParser' => PropertyParser::class,
            'PropertyHandler' => PropertyHandler::class,
            'ActionHandler' => ActionHandler::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        //
    }
}
