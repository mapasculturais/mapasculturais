<?php

class RegistrationFileConfigurationFormTest extends \PHPUnit\Framework\TestCase
{
    private function sourcePath(string $path): string
    {
        return realpath(__DIR__ . '/../src/' . $path)
            ?: realpath(__DIR__ . '/../../src/' . $path)
            ?: '';
    }

    private function opportunityModuleScript(): string
    {
        return file_get_contents($this->sourcePath('themes/BaseV1/assets/js/ng.entity.module.opportunity.js'));
    }

    public static function configurationForms(): array
    {
        return [
            'adicionar campo' => ['editbox-registration-fields', 'data.newFieldConfiguration', 'fieldType', false],
            'editar campo' => ['editbox-registration-field-{{field.id}}', 'field', 'fieldType', true],
            'adicionar anexo' => ['editbox-registration-files', 'data.newFileConfiguration', 'allowedFileTypes', false],
            'editar anexo' => ['editbox-registration-files-{{field.id}}', 'field', 'allowedFileTypes', true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('configurationForms')]
    public function testConfigurationFormsKeepTheSameOptionOrder(
        string $dialogId,
        string $model,
        string $typeProperty,
        bool $hasStep
    ): void {
        $partsPath = 'themes/BaseV1/layouts/parts/singles/';
        $template = file_get_contents($this->sourcePath($partsPath . 'opportunity-registrations--fields.php'));
        $matched = preg_match(
            '/<edit-box\b[^>]*\bid="' . preg_quote($dialogId, '/') . '"[^>]*>(.*?)<\/edit-box>/s',
            $template,
            $matches
        );
        $this->assertSame(1, $matched, "Formulário não encontrado: {$dialogId}");

        // Expande os controles compartilhados para verificar a ordem completa de cada formulário.
        $form = $matches[1];
        foreach ([
            'opportunity-registrations--fields--field-require',
            'opportunity-registrations-field--proponent',
            'opportunity-registrations-file--proponent',
        ] as $partial) {
            $form = str_replace(
                '$this->part(\'singles/' . $partial . '\');',
                file_get_contents($this->sourcePath($partsPath . $partial . '.php')),
                $form
            );
        }

        $controls = [
            "ng-model=\"{$model}.title\"",
            "ng-model=\"{$model}.description\"",
        ];
        if ($hasStep) {
            $controls[] = "ng-model=\"{$model}.step\"";
        }
        $typeBinding = $typeProperty === 'allowedFileTypes' ? 'checklist-model' : 'ng-model';
        $controls = array_merge($controls, [
            "{$typeBinding}=\"{$model}.{$typeProperty}\"",
            'ng-model="field.required"',
            'ng-model="field.conditional"',
            "checklist-model=\"{$model}.categories\"",
            "checklist-model=\"{$model}.registrationRanges\"",
            "checklist-model=\"{$model}.proponentTypes\"",
        ]);

        $previousPosition = -1;
        $previousControl = '';
        foreach ($controls as $control) {
            $position = strpos($form, $control);
            $this->assertNotFalse($position, "{$dialogId}: controle ausente: {$control}");
            $this->assertGreaterThan(
                $previousPosition,
                $position,
                "{$dialogId}: {$control} deve aparecer depois de {$previousControl}"
            );
            $previousPosition = $position;
            $previousControl = $control;
        }
    }

    public function testNewAttachmentResetKeepsObjectReferenceUsedByRequiredCheckbox(): void
    {
        $script = $this->opportunityModuleScript();

        $createFunctionStart = strpos($script, '$scope.createFileConfiguration = function()');
        $createFunctionEnd = strpos($script, '$scope.removeFileConfiguration = function', $createFunctionStart);
        $createFunction = substr($script, $createFunctionStart, $createFunctionEnd - $createFunctionStart);

        $this->assertStringContainsString(
            'angular.copy(fileConfigurationSkeleton, $scope.data.newFileConfiguration);',
            $createFunction,
            'O reset deve preservar a referência observada pelo alias field do template.'
        );
        $this->assertStringNotContainsString(
            '$scope.data.newFileConfiguration = angular.copy(fileConfigurationSkeleton);',
            $createFunction,
            'Substituir o objeto mantém o checkbox ligado à configuração anterior.'
        );
    }

    public function testNewFieldResetKeepsObjectReferenceUsedByRequiredCheckbox(): void
    {
        $script = $this->opportunityModuleScript();

        $createFunctionStart = strpos($script, '$scope.createFieldConfiguration = function()');
        $createFunctionEnd = strpos($script, '$scope.removeFieldConfiguration = function', $createFunctionStart);
        $createFunction = substr($script, $createFunctionStart, $createFunctionEnd - $createFunctionStart);

        $this->assertStringContainsString(
            'angular.copy(fieldConfigurationSkeleton, $scope.data.newFieldConfiguration);',
            $createFunction,
            'O reset deve preservar a referência observada pelo alias field do template.'
        );
        $this->assertStringNotContainsString(
            '$scope.data.newFieldConfiguration = angular.copy(fieldConfigurationSkeleton);',
            $createFunction,
            'Substituir o objeto mantém o checkbox ligado à configuração anterior.'
        );
    }
}
