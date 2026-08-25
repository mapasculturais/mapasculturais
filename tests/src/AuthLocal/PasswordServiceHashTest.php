<?php

namespace Tests\AuthLocal;

use LocalAuth\PasswordService;
use PHPUnit\Framework\TestCase;

/**
 * R1 (criação) — hashPassword pinado em PASSWORD_BCRYPT e o formato de saída
 * é o mesmo bcrypt self-contained do plugin. Compat-exata de formato:
 * $2y$ (ou $2a/$2b aceitos na LEITURA; a CRIAÇÃO pinada — teste de formato).
 */
class PasswordServiceHashTest extends TestCase
{
    use RequiresLocalAuthModule;

    private PasswordService $service;

    protected function setUp(): void
    {
        $this->guardLocalAuthModule();
        $this->service = new PasswordService([]);
    }

    public function testHashPasswordProducesBcrypt60Chars(): void
    {
        $hash = $this->service->hash('S3nh@Forte');
        $this->assertSame(60, strlen($hash));
        $this->assertMatchesRegularExpression('/^\$2y\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $hash, 'criação pinada em bcrypt $2y$');
    }

    public function testHashPasswordIsVerifiableRoundTrip(): void
    {
        $hash = $this->service->hash('SênhaÇão1@');
        $this->assertTrue($this->service->verify('SênhaÇão1@', $hash));
        $this->assertFalse($this->service->verify('outra', $hash));
    }

    public function testHashPasswordUsesRandomSaltPerCall(): void
    {
        $h1 = $this->service->hash('mesma-senha');
        $h2 = $this->service->hash('mesma-senha');
        $this->assertNotSame($h1, $h2, 'salt auto-gerado por chamada (propriedade do plugin)');
        $this->assertTrue($this->service->verify('mesma-senha', $h1));
        $this->assertTrue($this->service->verify('mesma-senha', $h2));
    }

    public function testHashPasswordTruncatesAt72BytesLikeBcrypt(): void
    {
        // bcrypt ignora além de 72 bytes — propriedade herdada (documentada, não bug)
        $long = str_repeat('a1B!', 30); // 120 chars
        $hash = $this->service->hash($long);
        $this->assertTrue($this->service->verify(substr($long, 0, 72) . 'EXTRA-IGNORED', $hash) === $this->service->verify($long, $hash));
    }
}
