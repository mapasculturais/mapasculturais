<?php

namespace Tests;

class AppealTechnicalScoreCorrectionComponentTest extends Abstract\TestCase
{
    public function testEvaluationPageContainsCompleteCorrectionWorkflow(): void
    {
        $componentPath = APPLICATION_PATH . 'modules/OpportunityAppealPhase/components/appeal-technical-score-correction/';
        $view = file_get_contents(APPLICATION_PATH . 'modules/Opportunities/views/registration/evaluation.php');

        $this->assertFileExists($componentPath . 'init.php');
        $this->assertFileExists($componentPath . 'script.js');
        $this->assertFileExists($componentPath . 'template.php');
        $this->assertFileExists($componentPath . 'texts.php');
        $this->assertStringContainsString('appeal-technical-score-correction', $view);

        $script = file_get_contents($componentPath . 'script.js');
        $template = file_get_contents($componentPath . 'template.php');
        $technicalScript = file_get_contents(APPLICATION_PATH . 'modules/EvaluationMethodTechnical/components/technical-evaluation-form/script.js');
        $technicalTemplate = file_get_contents(APPLICATION_PATH . 'modules/EvaluationMethodTechnical/components/technical-evaluation-form/template.php');
        $this->assertStringContainsString('technicalScoreCorrectionRelator', $script);
        $this->assertStringContainsString('technicalScoreCorrection', $script);
        $this->assertStringContainsString('resolveTechnicalScoreCorrection', $script);
        $this->assertStringContainsString('reopenTechnicalScoreCorrection', $script);
        $this->assertStringContainsString('response.status === 409', $script);
        $this->assertStringContainsString('type="checkbox"', $template);
        $this->assertStringContainsString('<fieldset', $template);
        $this->assertStringContainsString('confirmNoScoreChange', $template);
        $this->assertStringContainsString('history', $template);
        $this->assertStringContainsString('hasAppealTechnicalCorrection', $technicalScript);
        $this->assertStringContainsString('hasAppealTechnicalCorrection', $technicalTemplate);
    }
}
