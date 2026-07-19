<?php

class OpportunityWorkplanMonitoringSummaryStaticTest extends \PHPUnit\Framework\TestCase
{
    private function sourcePath(string $path): string
    {
        return realpath(__DIR__ . '/../src/' . $path)
            ?: realpath(__DIR__ . '/../../src/' . $path)
            ?: '';
    }

    public function testMonitoringValidationIsGuardedByReportingPhase(): void
    {
        $module = file_get_contents($this->sourcePath('modules/OpportunityWorkplan/Module.php'));

        // Validação de monitoramento deve estar presente no hook sendValidationErrors
        $this->assertStringContainsString('isReportingPhase', $module);
        $this->assertStringContainsString('executedHasCommunityCoauthors', $module);
        $this->assertStringContainsString('executedHasPressStrategy', $module);
        // Validação via validationErrors da entidade foi removida (causa duplicação)
        $this->assertStringNotContainsString('foreach ($delivery->validationErrors as $field => $messages)', $module);
        // Labels de campo devem existir para formatação das mensagens
        $this->assertStringContainsString("'executionDetail' => 'Detalhamento da execução da meta'", $module);
        $this->assertStringContainsString("'executedDocumentationTypes' => 'Tipos de documentação produzida'", $module);
    }
}
