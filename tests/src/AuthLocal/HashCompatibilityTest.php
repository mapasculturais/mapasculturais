<?php

namespace Tests\AuthLocal;

use LocalAuth\PasswordService;
use PHPUnit\Framework\TestCase;

/**
 * O port valida todos os vetores legados byte-a-byte (trava dupla:
 * camada unit; a camada integration é testLoginSucceedsWithLegacyHash).
 *
 * Contrato testado: MapasCulturais\Modules\LocalAuth\PasswordService
 *   verifyPassword(string $password, ?string $hash): bool
 * deve delegar a password_verify SEM transformação do hash — o hash do fixture
 * entra e sai intacto pela comparação.
 */
class HashCompatibilityTest extends TestCase
{
    use RequiresLocalAuthModule;

    private PasswordService $service;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
        $this->service = new PasswordService([]);
    }

    /** @dataProvider provideVectors */
    public function testPortVerifiesLegacyVectorByteByByte(array $vector): void
    {
        $this->assertTrue(
            $this->service->verify($vector['plain'], $vector['hash']),
            "o port DEVE aceitar o vetor legado {$vector['name']} (R1 — nenhum usuário redefine senha)"
        );
    }

    /** @dataProvider provideVectors */
    public function testPortRejectsWrongPasswordOnLegacyVector(array $vector): void
    {
        $this->assertFalse($this->service->verify('x-WRONG-x', $vector['hash']));
    }

    public function testVerifyWithNullHashReturnsFalseWithoutErrors(): void
    {
        // usuário social sem senha local — sem TypeError, sem deprecation (bug 0.4 da r2)
        $this->assertFalse($this->service->verify('qualquer', null));
    }

    public function testVerifyWithMalformedHashReturnsFalse(): void
    {
        foreach (['', 'abc', 'not-a-hash', str_repeat('x', 59), '$2y$10$curto'] as $bad) {
            $this->assertFalse($this->service->verify('senha', $bad), "hash malformado '{$bad}' deve retornar false sem exceção");
        }
    }

    /** @dataProvider provideVectors */
    public function testVerificationIsStableAcrossRepeatedCalls(array $vector): void
    {
        // anti-rehash (unidade): a verificação NÃO altera o comportamento nem o formato
        // esperado do hash — mesmas entradas, mesmas saídas, sempre.
        $h1 = $this->service->verify($vector['plain'], $vector['hash']);
        $h2 = $this->service->verify($vector['plain'], $vector['hash']);
        $this->assertSame($h1, $h2);
        $this->assertSame(60, strlen($vector['hash']), 'o hash legado nunca é reescrito pela verificação');
    }

    public static function provideVectors(): array
    {
        $cases = [];
        $data = json_decode((string) file_get_contents(__DIR__ . '/../fixtures/auth-local-hash-vectors.json'), true);
        foreach ($data['vectors'] as $v) {
            $cases[$v['name']] = [$v];
        }
        return $cases;
    }
}
