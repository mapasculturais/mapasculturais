<?php

namespace Tests\AuthLocal;

use LocalAuth\Module;
use PHPUnit\Framework\TestCase;
use Tests\AuthSecurityAppMock;

/**
 * Captcha com fetch INJETÁVEL (Module::setCaptchaFetch):
 * fail-closed preservado (sitekey configurada + indisponibilidade => false,
 * nunca aprova), sem rede. O fetch default (Guzzle timeout curto) é código de
 * produção intocado — estes casos exercitam apenas o seam.
 *
 * Unitário puro: o método verifyRecaptcha é private e lê $_POST — por isso
 * os casos abaixo cobrem o fluxo por reflexão sobre uma instância NÃO-booted
 * (newInstanceWithoutConstructor + localConfig falsificada), o que mantém o
 * teste livre de app/DB. O contrato completo (rota → captcha → login) é da
 * suíte de integração no CI.
 */
class RecaptchaVerifierTest extends TestCase
{
    use RequiresLocalAuthModule;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
    }

    private function moduleWithFetch(?\Closure $fetch, array $config = []): Module
    {
        AuthSecurityAppMock::install();
        $ref = new \ReflectionClass(Module::class);
        $module = $ref->newInstanceWithoutConstructor();

        $config += [
            'google-recaptcha-sitekey' => 'site-key-x',
            'google-recaptcha-secret' => 'secret-x',
        ];
        $prop = $ref->getProperty('localConfig');
        $prop->setAccessible(true);
        $prop->setValue($module, $config);

        if ($fetch !== null) {
            $module->setCaptchaFetch($fetch);
        }
        return $module;
    }

    private function invokeVerify(Module $module, ?string $token): bool
    {
        $_POST['g-recaptcha-response'] = $token ?? '';
        $m = new \ReflectionMethod($module, 'verifyRecaptcha');
        $m->setAccessible(true);
        try {
            return (bool) $m->invoke($module);
        } finally {
            unset($_POST['g-recaptcha-response']);
        }
    }

    public function testNoSitekeyMeansCaptchaDisabled(): void
    {
        $module = $this->moduleWithFetch(null, ['google-recaptcha-sitekey' => false]);
        $this->assertTrue($this->invokeVerify($module, null), 'sem sitekey o captcha está desligado (default do ambiente de teste)');
    }

    public function testSitekeyWithoutResponseTokenFails(): void
    {
        $module = $this->moduleWithFetch(fn (string $s, string $t): array => ['success' => true]);
        $this->assertFalse($this->invokeVerify($module, null), 'sitekey configurada exige resposta');
        $this->assertFalse($this->invokeVerify($module, ''), 'token vazio rejeitado sem nem chamar o fetch');
    }

    public function testSuccessfulVerificationPasses(): void
    {
        $module = $this->moduleWithFetch(fn (string $s, string $t): array => ['success' => true]);
        $this->assertTrue($this->invokeVerify($module, 'token-ok'));
    }

    public function testFailedVerificationRejects(): void
    {
        $module = $this->moduleWithFetch(fn (string $s, string $t): array => ['success' => false, 'error-codes' => ['invalid-input-response']]);
        $this->assertFalse($this->invokeVerify($module, 'token-invalido'));
    }

    public function testNetworkFailureFailsClosed(): void
    {
        $module = $this->moduleWithFetch(fn (string $s, string $t): array => throw new \RuntimeException('offline'));
        $this->assertFalse($this->invokeVerify($module, 'token'), 'indisponibilidade => falha fechada , nunca aprova');
    }

    public function testGarbageBodyFailsClosed(): void
    {
        $module = $this->moduleWithFetch(fn (string $s, string $t): array => []);
        $this->assertFalse($this->invokeVerify($module, 'token'), 'corpo sem success => rejeita');
    }

    public function testSecretAndTokenAreForwardedToFetch(): void
    {
        $seen = [];
        $module = $this->moduleWithFetch(function (string $s, string $t) use (&$seen): array {
            $seen = ['secret' => $s, 'token' => $t];
            return ['success' => true];
        });
        $this->assertTrue($this->invokeVerify($module, 'token-encaminhado'));
        $this->assertSame('secret-x', $seen['secret']);
        $this->assertSame('token-encaminhado', $seen['token']);
    }
}
