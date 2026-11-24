<?php

namespace Tests;

use MapasCulturais\Definitions\Metadata;
use Tests\Abstract\TestCase;

/**
 * Regressão: unserializer multiselect com valor vazio no banco.
 *
 * Com PHP 8+, `array_filter(null)` lança TypeError. Metadados gravados como
 * string vazia (ex.: space.acessibilidade_fisica = '') faziam json_decode
 * devolver null e derrubavam /api/space/find com 500.
 */
class MultiselectMetadataUnserializeRegressionTest extends TestCase
{
    private function unserialize(mixed $value): mixed
    {
        $definition = new Metadata('acessibilidade_fisica', [
            'label' => 'Acessibilidade física',
            'type' => 'multiselect',
        ]);

        return ($definition->unserialize)($value);
    }

    public function testEmptyStringDoesNotThrowAndReturnsEmptyList(): void
    {
        $this->assertSame([], $this->unserialize(''));
    }

    public function testNullReturnsEmptyList(): void
    {
        $this->assertSame([], $this->unserialize(null));
    }

    public function testEmptyJsonArrayReturnsEmptyList(): void
    {
        $this->assertSame([], $this->unserialize('[]'));
    }

    public function testJsonArrayIsPreserved(): void
    {
        $this->assertSame(['Rampas', 'Elevador'], $this->unserialize('["Rampas","Elevador"]'));
    }

    public function testLegacySemicolonSeparatedValuesRemainSupported(): void
    {
        $this->assertSame(['Rampas', 'Elevador'], $this->unserialize('Rampas;Elevador'));
    }

    public function testNonJsonStringFallsBackToLegacySemicolonToken(): void
    {
        // json_decode falha → mantém o fallback explode(';') já usado no legado
        $this->assertSame(['{not-json'], $this->unserialize('{not-json'));
    }

    public function testArrayInputIsFiltered(): void
    {
        $this->assertSame(['Rampas'], $this->unserialize(['Rampas', null, 'null', '']));
    }
}
