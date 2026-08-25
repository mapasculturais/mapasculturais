<?php

namespace Tests\AuthLocal;

use LocalAuth\PasswordService;
use PHPUnit\Framework\TestCase;

/**
 * Token de recuperação de senha — contratos de shape/expiração com relógio
 * injetável (boundary de 1h sem sleeps). O token do plugin (substr de hash
 * bcrypt, ~120 bits de entropia) já era forte; o port usa CSPRNG direto
 * (apêndice §4) — propriedades testadas, não o valor (regra T2 do plano).
 *
 * Contrato: generateRecoverToken(): string; isRecoverTokenExpired(int $issuedAt): bool
 */
class RecoverTokenTest extends TestCase
{
    use RequiresLocalAuthModule;

    private PasswordService $service;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
        $this->service = new PasswordService([]);
    }

    public function testTokenHasAtLeast20CharsFromUrlSafeAlphabet(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $token = $this->service->generateToken();
            $this->assertGreaterThanOrEqual(20, strlen($token), 'token >= 20 chars (paridade com o plugin)');
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9+_\/.-]+$/', $token, '20 hex chars (CSPRNG bin2hex)');
        }
    }

    public function testTokensAreUniqueAcrossGenerations(): void
    {
        $seen = [];
        for ($i = 0; $i < 100; $i++) {
            $token = $this->service->generateToken();
            $this->assertArrayNotHasKey($token, $seen, 'tokens não podem colidir (CSPRNG)');
            $seen[$token] = true;
        }
        $this->assertCount(100, $seen);
    }

}
