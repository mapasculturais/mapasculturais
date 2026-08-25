<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\RequestFactory;

/**
 * Contrato de RENDER do enqueue do recaptcha (determinístico: o enqueue roda
 * pré-render nos hooks -100).
 *
 * Regras (paridade plugin — gate por SITEKEY):
 *  - SEM sitekey: nenhuma tag do script do Google na página (index/register)
 *  - COM sitekey: script `recaptcha/api.js` PRESENTE na página (index/register)
 *  - SECRET jamais no HTML
 *  - Cessão: com provider Fake/Test a página do driver é PURA (fix5) — zero
 *    assets do módulo (incluindo o script do recaptcha)
 *
 * O ambiente de teste roda SEM sitekey (default); o ramo COM sitekey é coberto
 * mutando a config da instância do módulo (localConfig) por reflexão — mesmo
 * mecanismo do withSkeletonKeyEnabled.
 */
class LocalAuthRecaptchaRenderTest extends Abstract\TestCase
{
    use RequestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\LocalAuth\Module::class)) {
            $this->markTestSkipped('Módulo LocalAuth não carregável neste ambiente');
        }
        if (\env('AUTH_LOCAL_LOGIN_ENABLED', true) === false) {
            $this->markTestSkipped('suíte do estado ON');
        }
    }

    public function testNoSitekeyMeansNoRecaptchaScriptOnLocalPage(): void
    {
        // ambiente de teste usa provider 'Test' (driverOwnsLoginUi = true) — a
        // página COM UI local neste cenário é a /autenticacao/local
        $html = $this->renderPage('local');
        $this->assertStringContainsString('<login', $html, 'componente de login presente na página local');
        $this->assertStringNotContainsString('recaptcha/api.js', $html, 'sem sitekey configurado: script do Google NÃO é enfileirado (paridade plugin)');
    }

    public function testWithSitekeyScriptIsEnqueuedOnLocalPage(): void
    {
        $html = $this->withSitekey(fn () => $this->renderPage('local'));
        $this->assertStringContainsString('recaptcha/api.js', $html, 'com sitekey: script enfileirado na página (pré-render)');
        $pos = strpos($html, 'recaptcha/api.js');
        $body = strrpos($html, '</body>');
        $this->assertGreaterThan(0, $pos);
        $this->assertLessThan($body, $pos, 'script dentro do documento (antes do fechamento do body)');
    }

    public function testRegisterPageWithDriverOwnerYieldsNoModuleUi(): void
    {
        // cessão: com provider Test/Fake o register do módulo não renderiza a UI
        // dele (o status 200 vem de outra rota core de register — o contrato
        // testado é a AUSÊNCIA da UI/manifestação do módulo na resposta).
        // O enqueue com provider Local é coberto ao vivo em fix6-b-register.html.
        $request = $this->requestFactory->GET('auth', 'register');
        $this->app->run($request, false);
        $html = (string) App::i()->response->getBody();
        $this->assertStringNotContainsString('<create-account', $html, 'UI de cadastro do módulo ausente na cessão (é do driver)');
        $this->assertStringNotContainsString('recaptcha/api.js', $html, 'módulo não enfileira assets na página cedida');
    }

    public function testSecretNeverReachesHtml(): void
    {
        $html = $this->withSitekey(fn () => $this->renderPage('local'));
        $this->assertStringNotContainsString('google-recaptcha-secret', $html, 'chave de secret nunca no HTML');
    }

    public function testDriverOwnedIndexStaysPure(): void
    {
        // cessão: com provider Fake/Test a página index é do driver — o
        // módulo não enfileira NADA (nem recaptcha). O provider de teste é 'Test'
        // (tests/config.d/auth.php) = driverOwnsLoginUi() true.
        $request = $this->requestFactory->GET('auth', 'index');
        $this->app->run($request, false);
        $html = (string) App::i()->response->getBody();
        // o driver Test não renderiza UI própria de index (resposta do Test.php);
        // o contrato aqui é AUSÊNCIA de assets do módulo:
        $this->assertStringNotContainsString('local-auth.css', $html, 'página do driver sem CSS do módulo');
        $this->assertStringNotContainsString('recaptcha/api.js', $html, 'página do driver sem script do módulo');
    }

    // ==================== helpers ====================

    private function renderIndex(): string
    {
        return $this->renderPage('index');
    }

    private function renderPage(string $action): string
    {
        $request = $this->requestFactory->GET('auth', $action);
        $this->app->run($request, false);
        return (string) App::i()->response->getBody();
    }

    /** Executa $fn com sitekey ativo na instância do módulo (restaura ao fim). */
    private function withSitekey(callable $fn): mixed
    {
        $module = App::i()->modules['LocalAuth'] ?? null;
        if ($module === null) {
            return $fn();
        }
        $ref = new \ReflectionProperty($module, 'localConfig');
        $ref->setAccessible(true);
        $config = $ref->getValue($module);
        $config['google-recaptcha-sitekey'] = 'site-key-render-test';
        $ref->setValue($module, $config);
        try {
            return $fn();
        } finally {
            $config['google-recaptcha-sitekey'] = false;
            $ref->setValue($module, $config);
        }
    }
}
