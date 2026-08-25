<?php

namespace Tests\AuthLocal;

use LocalAuth\PasswordService;
use PHPUnit\Framework\TestCase;

/**
 * Política de senha server-side com regex COMPAT-EXATA do plugin
 * (lista exata de caracteres especiais preservada; ampliação futura só se
 * aceitar estritamente mais caracteres).
 *
 * Contrato: validatePasswordPolicy(string $password, string $confirmPassword): array
 * retorna lista de erros (vazio = ok). Flags de config com os mesmos nomes/defaults
 do plugin: passwordMustHave{CapitalLetters,LowercaseLetters,SpecialCharacters,Numbers},
 * minimumPasswordLength (default 6).
 */
class PasswordPolicyTest extends TestCase
{
    use RequiresLocalAuthModule;

    private array $allOn = [
        'minimumPasswordLength' => 6,
        'passwordMustHaveCapitalLetters' => true,
        'passwordMustHaveLowercaseLetters' => true,
        'passwordMustHaveSpecialCharacters' => true,
        'passwordMustHaveNumbers' => true,
    ];

    public function testAllFlagsOnAcceptsCompliantPassword(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertSame([], $svc->validatePolicy('S3nh@Forte', 'S3nh@Forte'));
    }

    public function testRejectsMissingNumber(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('Senh@Forte', 'Senh@Forte'));
    }

    public function testRejectsMissingCapitalLetter(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('s3nh@forte', 's3nh@forte'));
    }

    public function testRejectsMissingLowercaseLetter(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('S3NH@FORTE', 'S3NH@FORTE'));
    }

    public function testRejectsMissingSpecialCharacter(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('S3nhaForte', 'S3nhaForte'));
    }

    public function testRejectsShortPassword(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('S3n@', 'S3n@'));
    }

    public function testMinimumLengthBoundary(): void
    {
        $svc = new PasswordService(array_merge($this->allOn, ['minimumPasswordLength' => 8]));
        $this->assertNotSame([], $svc->validatePolicy('A1!aaaa', 'A1!aaaa'));   // 7 = N-1
        $this->assertSame([], $svc->validatePolicy('A1!aaaaa', 'A1!aaaaa')); // 8 = N
    }

    public function testEachFlagOffRemovesItsRequirement(): void
    {
        $passwords = [
            'passwordMustHaveNumbers' => 'Senh@Forte',           // sem número
            'passwordMustHaveCapitalLetters' => 's3nh@forte',    // sem maiúscula
            'passwordMustHaveLowercaseLetters' => 'S3NH@FORTE',  // sem minúscula
            'passwordMustHaveSpecialCharacters' => 'S3nhaForte', // sem especial
        ];
        foreach ($passwords as $flag => $plain) {
            $svc = new PasswordService(array_merge($this->allOn, [$flag => false]));
            $this->assertSame(
                [],
                $svc->validatePolicy($plain, $plain),
                "flag {$flag}=false deve remover o requisito correspondente"
            );
        }
    }

    /**
     * COMPAT-EXATA — a lista de especiais do plugin (Provider.php:608), replicada
     * verbatim no port. ATENÇÃO à quirk de byte: a regex NÃO usa /u, e caracteres
     * multi-byte listados (£ = C2A3, ¨ = C2A8, ´ = C2B4) fazem o BYTE C2 casar
     * sozinho — logo QUALQUER caractere UTF-8 começando com C2 (incluindo § = C2A7)
     * satisfaz o requisito de especial. ISSO É O COMPORTAMENTO DO PLUGIN (mesma
     * regex, mesmo byte-matching) — compat-exata exige preservar inclusive a quirk.
     * Endurecer a regex (modo /u, lista estrita) é etapa FUTURA e deve aceitar
     * estritamente MAIS caracteres, nunca menos.
     */
    public function testSpecialCharactersRegexMatrixCompatExata(): void
    {
        $svc = new PasswordService($this->allOn);
        $in = ['@', '#', '!', '$', '%', '&', '*', '(', ')', '{', '}', '?', '>', '<', ',', '|', '=', '_', '"', '+', '`', '´', '¨', '£', '[', ']', '.', ';', ':', '/', '-', '^', '~'];
        foreach ($in as $special) {
            $plain = 'A1' . $special . 'aaaa';
            $this->assertSame(
                [],
                $svc->validatePolicy($plain, $plain),
                "'{$special}' ESTÁ na lista do plugin e deve satisfazer o requisito de especial"
            );
        }
        // quirk de byte multi-byte (C2): § NÃO está na lista, mas casa pelo byte
        // compartilhado com £/¨/´ — comportamento IDÊNTICO ao plugin (documentado)
        $this->assertSame(
            [],
            $svc->validatePolicy('A1§aaaa', 'A1§aaaa'),
            'quirk preservada: § casa por colisão de byte C2 (compat-exata com o plugin)'
        );
        // caracteres que NEM os bytes individuais estão na classe continuam rejeitados
        foreach (['A1éaaaa', 'A1ñaaaa', 'A1aaaa'] as $plain) {
            $this->assertNotSame(
                [],
                $svc->validatePolicy($plain, $plain),
                "'{$plain}' não contém especial (nem por colisão de byte) e deve ser rejeitado"
            );
        }
    }

    public function testPasswordsDoNotMatchIsAnError(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('S3nh@Forte', 'D1ferente#'));
    }

    public function testEmptyPasswordIsAnError(): void
    {
        $svc = new PasswordService($this->allOn);
        $this->assertNotSame([], $svc->validatePolicy('', ''));
    }
}
