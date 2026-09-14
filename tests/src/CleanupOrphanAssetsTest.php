<?php

class CleanupOrphanAssetsTest extends \PHPUnit\Framework\TestCase
{
    private function sourcePath(string $path): string
    {
        return realpath(__DIR__ . '/../src/' . $path)
            ?: realpath(__DIR__ . '/../../src/' . $path)
            ?: '';
    }

    public function testCachedHtmlTemplateIsProtectedFromCleanup(): void
    {
        $script = file_get_contents($this->sourcePath('tools/cleanup-orphan-assets.php'));
        preg_match('#preg_match_all\(\'(/.+?/i)\', \$value#', $script, $pattern);

        $cachedUrl = serialize('https://mapa.cultura.gov.br/assets/mapa.cultura.gov.br/html/edit-box.directives.511rbu.html');
        preg_match_all($pattern[1], $cachedUrl, $protected);

        $this->assertContains(
            'edit-box.directives.511rbu.html',
            $protected[0],
            'O template com a URL em cache não pode ser removido pela limpeza, senão o editor de formulário recebe 404.'
        );
    }
}
