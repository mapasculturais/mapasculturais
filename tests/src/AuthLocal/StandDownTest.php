<?php

namespace Tests\AuthLocal;

use LocalAuth\Module;
use PHPUnit\Framework\TestCase;
use Tests\AuthSecurityAppMock;

/**
 * Stand-down de coexistência — UNITÁRIO da detecção com config
 * falsificada via AppMock (SEM boot do MLA; restrição T9: nenhum teste inclui
 * arquivos de src/plugins/). Métodos estáticos entregues:
 *   Module::multipleLocalAuthActive(): bool  (lê config plugins/auth.provider)
 *   Module::isEnabled(): bool                 (lê env AUTH_LOCAL_LOGIN_ENABLED)
 */
class StandDownTest extends TestCase
{
    use RequiresLocalAuthModule;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
        AuthSecurityAppMock::install();
        unset($_ENV['AUTH_LOCAL_LOGIN_ENABLED']);
    }

    protected function tearDown(): void
    {
        unset($_ENV['AUTH_LOCAL_LOGIN_ENABLED']);
    }

    public function testStandsDownWhenMlaPluginEnabledInConfig(): void
    {
        AppMockConfig::set(['plugins' => ['MultipleLocalAuth']]);
        $this->assertTrue(Module::multipleLocalAuthActive(), 'plugin MLA na config de plugins => stand-down');
    }

    public function testStandsDownWhenAuthProviderIsMla(): void
    {
        AppMockConfig::set(['plugins' => [], 'auth.provider' => 'MultipleLocalAuth\\Provider']);
        $this->assertTrue(Module::multipleLocalAuthActive(), 'auth.provider = MLA => stand-down');
    }

    public function testStandsDownForShortProviderName(): void
    {
        AppMockConfig::set(['plugins' => [], 'auth.provider' => 'MultipleLocalAuth']);
        $this->assertTrue(Module::multipleLocalAuthActive());
    }

    public function testNoStandDownInCleanInstall(): void
    {
        AppMockConfig::set(['plugins' => [], 'auth.provider' => 'OpauthLoginCidadao']);
        $this->assertFalse(Module::multipleLocalAuthActive(), 'sem MLA: módulo core ativo');
    }

    public function testToggleEnabledByDefaultAndOffByEnv(): void
    {
        $this->assertTrue(Module::isEnabled(), 'default do sponsor: AUTH_LOCAL_LOGIN_ENABLED=true');
        $_ENV['AUTH_LOCAL_LOGIN_ENABLED'] = 'false';
        $this->assertFalse(Module::isEnabled(), 'env false desliga o módulo no boot (rotas não registradas)');
        $_ENV['AUTH_LOCAL_LOGIN_ENABLED'] = '0';
        $this->assertFalse(Module::isEnabled(), 'env 0 também desliga');
        $_ENV['AUTH_LOCAL_LOGIN_ENABLED'] = 'true';
        $this->assertTrue(Module::isEnabled());
    }
}

/** Helper interno: sobrescreve a config do AppMock para o caso corrente. */
class AppMockConfig
{
    public static function set(array $config): void
    {
        $app = \MapasCulturais\App::i();
        $app->config = ['__auth_security_mock' => true, 'app.mode' => 'development', 'base.url' => 'https://mapas.test/'] + $config;
    }
}
