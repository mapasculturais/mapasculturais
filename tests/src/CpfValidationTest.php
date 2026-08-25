<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\RequestFactory;

/**
 * Validação de CPF (matriz matemática) via método público do módulo
 * entregue (LocalAuth\Module::validCpf — instância booted no ambiente de teste).
 *
 * CPFs de teste são exemplos MATEMÁTICOS (check-digit válido, não registrados
 * a pessoas) — gerados por script, nunca dados reais.
 */
class CpfValidationTest extends Abstract\TestCase
{
    use RequestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\LocalAuth\Module::class)) {
            $this->markTestSkipped('Módulo LocalAuth não carregável neste ambiente (prefixo de autoload ausente)');
        }
    }

    private function module(): \LocalAuth\Module
    {
        $module = App::i()->modules['LocalAuth'] ?? null;
        $this->assertNotNull($module, 'módulo LocalAuth registrado no ambiente de teste');
        return $module;
    }

    /** @dataProvider provideValidCpfs */
    public function testAcceptsValidCpfWithOrWithoutMask(string $cpf): void
    {
        $this->assertTrue($this->module()->validCpf($cpf), "CPF válido com máscara: {$cpf}");
        $digits = preg_replace('/\D/', '', $cpf);
        $this->assertTrue($this->module()->validCpf($digits), "CPF válido sem máscara: {$digits}");
    }

    public static function provideValidCpfs(): array
    {
        return [
            '529.982.247-25' => ['529.982.247-25'],
            '111.444.777-35' => ['111.444.777-35'],
            '390.533.447-05' => ['390.533.447-05'],
            '123.456.789-09' => ['123.456.789-09'],
            '987.654.321-00' => ['987.654.321-00'],
            '248.688.969-89' => ['248.688.969-89'],
        ];
    }

    /** @dataProvider provideRepeatedCpfs */
    public function testRejectsRepeatedDigits(string $cpf): void
    {
        $this->assertFalse($this->module()->validCpf($cpf), "sequência repetida rejeitada: {$cpf}");
    }

    public static function provideRepeatedCpfs(): array
    {
        return [
            '111.111.111-11' => ['111.111.111-11'],
            '22222222222' => ['22222222222'],
            '999.999.999-99' => ['999.999.999-99'],
            '00000000000' => ['00000000000'],
        ];
    }

    public function testRejectsWrongCheckDigit(): void
    {
        $this->assertFalse($this->module()->validCpf('529.982.247-24'));
        $this->assertFalse($this->module()->validCpf('529.982.247-26'));
        $this->assertFalse($this->module()->validCpf('52998224724'));
    }

    public function testRejectsWrongLength(): void
    {
        $this->assertFalse($this->module()->validCpf('529.982.247'));
        $this->assertFalse($this->module()->validCpf('529982247'));
        $this->assertFalse($this->module()->validCpf('529.982.247-253'));
        $this->assertFalse($this->module()->validCpf(''));
    }

    public function testRejectsNonNumericInput(): void
    {
        $this->assertFalse($this->module()->validCpf('abc.def.ghi-jk'));
        $this->assertFalse($this->module()->validCpf('529.982.247-2x'));
    }
}
