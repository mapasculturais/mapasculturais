<?php

namespace Tests;

use Tests\Abstract\TestCase;

/**
 * Regressão: com editableFields preenchido, o entity-field marcava qualquer prop
 * fora da lista como readonly — inclusive editableUntil no modal do gestor
 * ("Configurar campos editáveis"), travando a data e impedindo reabrir prazo.
 *
 * Não há runner de Vue nesta suíte; o teste garante que a exceção dos metadados
 * de configuração permanece no script do componente.
 */
class EntityFieldEditableUntilReadonlyTest extends TestCase
{
    private function entityFieldScript(): string
    {
        $sourceRoot = realpath(__DIR__ . '/../../src') ?: realpath(__DIR__ . '/../src');
        $path = $sourceRoot . '/modules/Entities/components/entity-field/script.js';
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function testFieldEditConfigPropsAreExcludedFromEditableFieldsLock(): void
    {
        $script = $this->entityFieldScript();

        $this->assertStringContainsString(
            "fieldEditConfigProps",
            $script,
            'A exclusão dos metadados de abertura de edição deve existir em isReadonly'
        );

        foreach (['editableUntil', 'editableFields', 'editSentTimestamp'] as $prop) {
            $this->assertStringContainsString(
                "'{$prop}'",
                $script,
                "A prop {$prop} deve ficar fora do lock de editableFields"
            );
        }

        $this->assertStringContainsString(
            'isFieldEditConfigProp',
            $script,
            'O lock de editableFields/allowedFields deve respeitar isFieldEditConfigProp'
        );

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*!isFieldEditConfigProp\s*\)/',
            $script,
            'O bloco que aplica o lock não deve rodar para metadados de configuração'
        );
    }
}
