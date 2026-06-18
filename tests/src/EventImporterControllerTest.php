<?php

namespace Tests;

use EventImporter\Controller;
use Tests\Abstract\TestCase;

class EventImporterControllerTest extends TestCase
{
    function testOccurrenceEndDateTimeUsesEndsOnWhenProvided(): void
    {
        $controller = Controller::i('eventimporter');

        $value = [
            'STARTS_ON' => '2026-06-12',
            'ENDS_ON' => '2026-06-13',
            'STARTS_AT' => '18:00',
            'ENDS_AT' => '01:00',
        ];

        $this->assertEquals(
            '2026-06-13 01:00',
            $controller->getOccurrenceEndDateTime($value)->format('Y-m-d H:i')
        );
    }

    function testOccurrenceDurationConsidersEndsOn(): void
    {
        $controller = Controller::i('eventimporter');

        $value = [
            'STARTS_ON' => '2026-06-12',
            'ENDS_ON' => '2026-06-13',
            'STARTS_AT' => '18:00',
            'ENDS_AT' => '01:00',
        ];

        $this->assertEquals(420, $controller->getOccurrenceDurationInMinutes($value));
    }
}
