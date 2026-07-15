<?php

namespace Tests;

class AppealTechnicalCorrectionRoutesTest extends Abstract\TestCase
{
    public function testModuleRegistersCompleteCorrectionApi(): void
    {
        $module = file_get_contents(
            APPLICATION_PATH . 'modules/OpportunityAppealPhase/Module.php'
        );

        $this->assertStringContainsString('GET(registration.technicalScoreCorrection)', $module);
        $this->assertStringContainsString('PATCH(registration.technicalScoreCorrectionRelator)', $module);
        $this->assertStringContainsString('PATCH(registration.technicalScoreCorrection)', $module);
        $this->assertStringContainsString('POST(registration.resolveTechnicalScoreCorrection)', $module);
        $this->assertStringContainsString('POST(registration.reopenTechnicalScoreCorrection)', $module);
        $this->assertStringContainsString('AppealTechnicalCorrectionConflict', $module);
        $this->assertStringContainsString('409', $module);
        $this->assertStringContainsString('422', $module);
        $this->assertStringContainsString('entity(RegistrationEvaluation).send:before', $module);
    }
}
