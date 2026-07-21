<?php

class RegistrationFileConfigurationFormTest extends \PHPUnit\Framework\TestCase
{
    private function sourcePath(string $path): string
    {
        return realpath(__DIR__ . '/../src/' . $path)
            ?: realpath(__DIR__ . '/../../src/' . $path)
            ?: '';
    }

    public function testNewAttachmentResetKeepsObjectReferenceUsedByRequiredCheckbox(): void
    {
        $script = file_get_contents($this->sourcePath('themes/BaseV1/assets/js/ng.entity.module.opportunity.js'));

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
}
