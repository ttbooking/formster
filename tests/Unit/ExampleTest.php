<?php

use TTBooking\Formster\Entities\Aura;
use TTBooking\Formster\Facades\PropertyParser;

/**
 * Hello World!
 *
 * This is a description.
 */
class ExampleTest {}

beforeEach(function () {
    $this->aura = PropertyParser::parser('phpdoc')->parse(ExampleTest::class);
});

test('example', function () {
    expect($this->aura)->toBeInstanceOf(Aura::class)
        ->and($this->aura->summary)->toBe('Hello World!')
        ->and($this->aura->description)->toBe('This is a description.');
});
