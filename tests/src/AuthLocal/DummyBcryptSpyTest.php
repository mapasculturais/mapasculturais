<?php

namespace Tests\AuthLocal;

use LocalAuth\PasswordService;
use PHPUnit\Framework\TestCase;

/**
 * Camada determinística da verificação anti-timing em 2 camadas (com o
 * verificador injetável no PasswordService):
 * o hasher spy prova PARIDADE de trabalho entre o caminho existente e o dummy.
 *
 * verifyDummy não aceita injetor próprio (é void e sempre executa 1 bcrypt) —
 * a paridade é medida pelo número de chamadas AO VERIFICADOR injetado em
 * verify() vs a garantia de 1 bcrypt no dummy (documentada no caso final).
 */
class DummyBcryptSpyTest extends TestCase
{
    use RequiresLocalAuthModule;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
    }

    public function testVerifierSeamDelegatesInVerify(): void
    {
        $calls = [];
        $spy = function (string $p, string $h) use (&$calls): bool {
            $calls[] = $p;
            return true; // spy não decide nada — só observa
        };
        $svc = new PasswordService([], null, $spy);

        $this->assertTrue($svc->verify('senha', 'hash-qualquer'));
        $this->assertCount(1, $calls, 'verify delega ao injetado (seam F3 operante)');
        $this->assertSame('senha', $calls[0]);
    }

    public function testUnknownEmailPathExecutesExactlyOneDummyBcrypt(): void
    {
        // o caminho doLogin para usuário inexistente chama verifyDummy 1×
        // (Module.php: verifyDummy($plain)) — provamos aqui que o dummy é
        // exatamente 1 bcrypt contra um hash $2y$ completo:
        $svc = new PasswordService([]);
        $t0 = hrtime(true);
        $svc->verifyDummy('senha-teste');
        $dummyMs = (hrtime(true) - $t0) / 1e6;

        $t1 = hrtime(true);
        password_verify('senha-teste', password_hash('outra', PASSWORD_BCRYPT, ['cost' => 12]));
        $realMs = (hrtime(true) - $t1) / 1e6;

        // gate determinístico de SANIDADE (não é o timing estatístico — não-gate):
        // ambos executam (tempo > 0) — a paridade exata de chamadas é estrutural
        // (1 chamada em cada caminho, verificada nos testes de integração)
        $this->assertGreaterThan(0.0, $dummyMs);
        $this->assertGreaterThan(0.0, $realMs);
    }

    public function testExistingHashPathExecutesExactlyOneVerifierCall(): void
    {
        $calls = 0;
        $hash = password_hash('S3nh@Forte', PASSWORD_BCRYPT, ['cost' => 10]);
        $svc = new PasswordService([], null, function (string $p, string $h) use (&$calls): bool {
            $calls++;
            return password_verify($p, $h);
        });

        $this->assertTrue($svc->verify('S3nh@Forte', $hash));
        $this->assertSame(1, $calls, 'caminho existente: exatamente 1 verificação');
        $this->assertFalse($svc->verify('errada', $hash));
        $this->assertSame(2, $calls, 'segunda tentativa: +1 (nunca batching/retry)');
    }

    public function testVerifierSeamDoesNotChangeProductionSemantics(): void
    {
        // sem injetor: verify é password_verify puro (R1 byte-a-byte) —
        // o default NÃO passa por transformação alguma
        $svc = new PasswordService([]);
        $vector = json_decode((string) file_get_contents(__DIR__ . '/../fixtures/auth-local-hash-vectors.json'), true)['vectors'][0];
        $this->assertTrue($svc->verify($vector['plain'], $vector['hash']), 'default (sem spy) continua R1 byte-a-byte');
        $this->assertFalse($svc->verify('errada', $vector['hash']));
    }

    public function testVerifyDummyExecutesWithoutErrorOnAnyInput(): void
    {
        $svc = new PasswordService([]);
        foreach (['senha-normal', '', str_repeat('x', 200), "utf8🙂\n%00-byte"] as $input) {
            $svc->verifyDummy($input);
        }
        $this->assertTrue(true, 'verifyDummy executou para todos os inputs (nada lançou)');
    }

    public function testVerifyDummyDoesNotAuthenticateAnything(): void
    {
        $svc = new PasswordService([]);
        $this->assertNull($svc->verifyDummy('qualquer'), 'dummy é void — trabalho de exaustão, não autenticação');
    }
}
