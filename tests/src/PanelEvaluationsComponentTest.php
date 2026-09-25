<?php

namespace Test;

use Tests\Abstract\TestCase;

class PanelEvaluationsComponentTest extends TestCase
{
    function testEvaluationsQueryIncludesAppealPhases(): void
    {
        $sourceRoot = realpath(__DIR__ . '/../../src') ?: realpath(__DIR__ . '/../src');

        $component = file_get_contents(
            $sourceRoot . '/modules/Opportunities/components/panel--evaluations-tabs/script.js'
        );

        $this->assertStringContainsString(
            "'status': 'IN(1,-1,-20)'",
            $component,
            'A listagem de avaliações deve incluir fases de recurso'
        );
    }
}
