<?php

use PHPUnit\Framework\TestCase;

class OpportunityPhaseDatesVisibilityStaticTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testOpportunityRegistersHidePhaseDatesMetadata(): void
    {
        $module = file_get_contents($this->root . '/src/modules/Opportunities/Module.php');
        $metadataStart = strpos($module, "registerOpportunityMetadata('hidePhaseDates'");
        $metadataEnd = strpos($module, "registerOpportunityMetadata('publicityOnly'", $metadataStart);
        $metadata = substr($module, $metadataStart, $metadataEnd - $metadataStart);

        $this->assertStringContainsString("registerOpportunityMetadata('hidePhaseDates'", $module);
        $this->assertStringContainsString("Ocultar datas das fases para o público", $module);
        $this->assertStringContainsString("'default' => false", $metadata);
        $this->assertStringNotContainsString("'description'", $metadata);
    }

    public function testOpportunityConfigurationShowsHidePhaseDatesFaqInfo(): void
    {
        $template = file_get_contents($this->root . '/src/modules/Opportunities/components/opportunity-phase-config-data-collection/template.php');
        $ptBrFaq = file_get_contents($this->root . '/src/modules/FAQ/questions/pt_BR/editais-oportunidades/configuracoes/ocultar-datas-fases.md');
        $esFaq = file_get_contents($this->root . '/src/modules/FAQ/questions/es_ES/editais-oportunidades/configuracoes/ocultar-datas-fases.md');

        $this->assertStringContainsString('prop="hidePhaseDates"', $template);
        $this->assertStringContainsString('<template #info>', $template);
        $this->assertStringContainsString("editais-oportunidades -> configuracoes -> ocultar-datas-fases", $template);
        $this->assertStringContainsString('O que significa ocultar datas das fases para o público?', $ptBrFaq);
        $this->assertStringContainsString('¿Qué significa ocultar las fechas de las fases para el público?', $esFaq);
    }

    public function testPhasesPayloadExposesHidePhaseDatesToTimelineConsumers(): void
    {
        $module = file_get_contents($this->root . '/src/modules/OpportunityPhases/Module.php');

        $this->assertMatchesRegularExpression(
            '/simplify\("[^"]*registrationFrom,registrationTo,isContinuousFlow,hasEndDate,hidePhaseDates,isDataCollection/s',
            $module
        );
        $this->assertMatchesRegularExpression(
            "/simplify\\('id,isFirstPhase,isLastPhase,isReportingPhase,isLastReportingPhase,isContinuousFlow,hasEndDate,hidePhaseDates,files/s",
            $module
        );
    }

    public function testTimelineDatesRespectHidePhaseDatesFlag(): void
    {
        $template = file_get_contents($this->root . '/src/modules/Opportunities/components/opportunity-phases-timeline/template.php');
        $script = file_get_contents($this->root . '/src/modules/Opportunities/components/opportunity-phases-timeline/script.js');

        $this->assertStringContainsString('showPhaseDates(item)', $script);
        $this->assertStringContainsString('!item.isLastPhase && showPhaseDates(item)', $template);
        $this->assertStringContainsString('item.isLastPhase && showPhaseDates(item) && item.publishTimestamp', $template);
    }

    public function testRegistrationAppealDatesRespectHidePhaseDatesFlag(): void
    {
        $template = file_get_contents($this->root . '/src/modules/Opportunities/components/registration-status/template.php');
        $script = file_get_contents($this->root . '/src/modules/Opportunities/components/registration-status/script.js');

        $this->assertStringContainsString('showPhaseDates()', $script);
        $this->assertStringContainsString('v-if="showPhaseDates()"', $template);
    }

    public function testRegistrationViewDeadlineMessagesRespectHidePhaseDatesFlag(): void
    {
        $view = file_get_contents($this->root . '/src/modules/Opportunities/views/registration/single.php');

        $this->assertStringContainsString('$hide_phase_dates = $entity->opportunity->firstPhase->hidePhaseDates;', $view);
        $this->assertStringContainsString('if(!$hide_phase_dates):', $view);
        $this->assertStringContainsString('O prazo para envio dessa inscrição foi encerrado', $view);
    }
}
