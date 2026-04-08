<?php

class OpportunityWorkplanMonitoringSummaryStaticTest extends \PHPUnit\Framework\TestCase
{
    public function testMonitoringValidationSummaryIsDerivedFromStructuredEntityErrors(): void
    {
        $module = file_get_contents(__DIR__ . '/../../src/modules/OpportunityWorkplan/Module.php');

        $this->assertStringContainsString('foreach ($goal->validationErrors as $field => $messages)', $module);
        $this->assertStringContainsString('foreach ($delivery->validationErrors as $field => $messages)', $module);
        $this->assertStringContainsString("\$appendEntityValidationSummary('goal', 'meta'", $module);
        $this->assertStringContainsString("\$appendEntityValidationSummary('delivery', 'entrega'", $module);
        $this->assertStringContainsString("'executionDetail' => 'Detalhamento da execução da meta'", $module);
        $this->assertStringContainsString("'executedHasPressStrategy' => 'Estratégia executada de relacionamento com imprensa'", $module);
        $this->assertStringContainsString("'executedDocumentationTypes' => 'Tipos de documentação produzida'", $module);
    }
}
