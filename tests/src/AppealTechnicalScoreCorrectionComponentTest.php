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
        $this->assertStringContainsString('<output v-if="loading" class="col-12">', $template);
        $this->assertStringNotContainsString('role="status"', $template);
        $this->assertStringContainsString('hasAppealTechnicalCorrection', $technicalScript);
        $this->assertStringContainsString('hasAppealTechnicalCorrection', $technicalTemplate);
    }

    public function testUserFacingTerminologyUsesEvaluatorInsteadOfRelator(): void
    {
        $modulePath = APPLICATION_PATH . 'modules/OpportunityAppealPhase/';
        $template = file_get_contents($modulePath . 'components/appeal-technical-score-correction/template.php');
        $texts = file_get_contents($modulePath . 'components/appeal-technical-score-correction/texts.php');
        $module = file_get_contents($modulePath . 'Module.php');
        $service = file_get_contents($modulePath . 'Services/AppealTechnicalCorrectionService.php');

        $this->assertStringContainsString('Avaliador responsável pela correção', $template);
        $this->assertStringContainsString('Definir avaliador', $template);
        $this->assertStringContainsString('Somente o avaliador designado', $template);
        $this->assertStringContainsString('Defina o avaliador para habilitar', $template);
        $this->assertStringContainsString('Avaliador definido.', $texts);
        $this->assertStringContainsString('O avaliador deve finalizar por último', $module);
        $this->assertStringContainsString('O avaliador informado não foi encontrado.', $module);
        $this->assertStringContainsString('Avaliador da correção de nota técnica', $module);
        $this->assertStringContainsString('designar avaliador', $service);
        $this->assertStringContainsString('O avaliador deve estar distribuído', $service);
        $this->assertStringContainsString('o avaliador alterado após a reabertura', $service);
        $this->assertStringContainsString('Defina um avaliador antes de reabrir', $service);
        $this->assertStringContainsString('O avaliador não está mais distribuído', $service);
        $this->assertStringContainsString('antes do avaliador.', $service);
    }
}
