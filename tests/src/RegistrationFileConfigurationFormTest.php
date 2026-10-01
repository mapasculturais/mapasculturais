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
