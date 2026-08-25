<?php

namespace Tests\AuthLocal;

use PHPUnit\Framework\TestCase;

/**
 * Integridade da fixture de vetores de compatibilidade de hash.
 *
 * Executa SEM o módulo LocalAuth (valida a própria fixture com o
 * password_verify puro do PHP): os vetores são o alicerce do contrato de compat —
 * "o port valida senha contra hash legado byte-a-byte". Se este teste falha,
 * a fixture foi corrompida/regenerada — e regenerar a fixture é PROIBIDO
 * (vetores são imutáveis; ver auth-local-hash-vectors.PROVENIENCE.md).
 */
class HashVectorsFixtureTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!file_exists(__DIR__ . '/../fixtures/auth-local-hash-vectors.json')) {
            self::fail('fixture auth-local-hash-vectors.json ausente');
        }
    }

    public function testFixtureHasTheThreeRequiredHashProfiles(): void
    {
        $data = self::fixture();
        $kinds = [];
        foreach ($data['vectors'] as $v) {
            $kinds[$v['kind']] = ($kinds[$v['kind']] ?? 0) + 1;
        }
        ksort($kinds);
        $this->assertSame(
            ['default-cost12-php84' => 6, 'interop-2a' => 6, 'legacy-cost10' => 6],
            $kinds,
            'A fixture exige os perfis $2y$12 (default PHP 8.4), $2y$10 (legado) e $2a$ (interop)'
        );
    }

    /** @dataProvider provideVectors */
    public function testEveryVectorVerifiesWithPlainPassword(array $vector): void
    {
        $this->assertTrue(
            password_verify($vector['plain'], $vector['hash']),
            "vetor {$vector['name']} deve validar (password_verify byte-a-byte)"
        );
    }

    /** @dataProvider provideVectors */
    public function testEveryVectorRejectsWrongPassword(array $vector): void
    {
        $this->assertFalse(password_verify('x-WRONG-x', $vector['hash']));
    }

    /** @dataProvider provideVectors */
    public function testVectorHashFormatIs60CharBcrypt(array $vector): void
    {
        $this->assertSame(60, strlen($vector['hash']));
        $this->assertMatchesRegularExpression('/^\$2[ay]\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $vector['hash']);
    }

    public function testLegacyPlaintextsAreInventedNotReal(): void
    {
        // sanity: nenhum vetor pode ter senha vazia/curta demais — são senhas de teste inventadas
        foreach (self::fixture()['vectors'] as $v) {
            $this->assertGreaterThanOrEqual(6, strlen($v['plain']));
        }
    }

    public static function provideVectors(): array
    {
        $cases = [];
        foreach (self::fixture()['vectors'] as $v) {
            $cases[$v['name']] = [$v];
        }
        return $cases;
    }

    private static function fixture(): array
    {
        static $data = null;
        if ($data === null) {
            $data = json_decode(
                (string) file_get_contents(__DIR__ . '/../fixtures/auth-local-hash-vectors.json'),
                true
            );
            self::assertIsArray($data, 'fixture JSON inválida');
            self::assertNotEmpty($data['vectors']);
        }
        return $data;
    }
}
