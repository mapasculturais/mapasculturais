<?php

namespace Test;

use MapasCulturais\Modules\Opportunities\RegistrationPdfFormatter;
use Tests\Abstract\TestCase;

class RegistrationPdfFormatterTest extends TestCase
{
    public function testFormatsLocationFieldAsReadableLines(): void
    {
        $config = (object) [
            'fieldType' => 'agent-owner-field',
            'config' => [
                'entityField' => '@location',
            ],
        ];

        $value = [
            'address_postalCode' => '87305447',
            'address_level0' => 'BR',
            'address_level1' => null,
            'address_level2' => 'PR',
            'address_level3' => null,
            'address_level4' => 'Campo Mourão',
            'address_level5' => null,
            'address_level6' => 'Jardim Veneza',
            'address_line1' => 'Rua Izabel Montesino, 109',
            'address_line2' => 'Casa',
            'endereco' => 'Rua Izabel Montesino, 109 - Casa - Jardim Veneza - Campo Mourão - PR - CEP: 87305-447',
            'location' => [
                'latitude' => '24.0573034',
                'longitude' => '-52.4080922',
            ],
            'publicLocation' => true,
            'En_Pais' => 'BR',
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, $value);

        $this->assertSame(
            implode("\n", [
                'Código postal: 87305-447',
                'País: BR',
                'Estado/Província: PR',
                'Município/Cidade/Comune: Campo Mourão',
                'Bairro: Jardim Veneza',
                'Endereço: Rua Izabel Montesino, 109',
                'Complemento: Casa',
                'endereco: Rua Izabel Montesino, 109 - Casa - Jardim Veneza - Campo Mourão - PR - CEP: 87305-447',
            ]),
            $formatted
        );

        $this->assertStringNotContainsString('address_postalCode', $formatted);
        $this->assertStringNotContainsString('latitude', $formatted);
        $this->assertStringNotContainsString('publicLocation', $formatted);
    }

    public function testFormatsCustomTableFieldAsHtmlTable(): void
    {
        $config = (object) [
            'fieldType' => 'custom-table',
            'config' => [
                'columns' => [
                    ['name' => 'Nome', 'type' => 'text'],
                    ['name' => 'Nascimento', 'type' => 'date'],
                    ['name' => 'Função', 'type' => 'select'],
                ],
            ],
        ];

        $value = [
            [
                'col0' => 'Maria Silva',
                'col1' => '1990-05-12',
                'col2' => 'Coordenação',
            ],
            [
                'col0' => 'João Souza',
                'col1' => '1988-11-30',
                'col2' => 'Produção',
            ],
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValueAsHtml($config, $value);

        $this->assertStringContainsString('<table', $formatted);
        $this->assertStringContainsString('<th>Nome</th>', $formatted);
        $this->assertStringContainsString('<th>Nascimento</th>', $formatted);
        $this->assertStringContainsString('<td>Maria Silva</td>', $formatted);
        $this->assertStringContainsString('<td>12/05/1990</td>', $formatted);
        $this->assertStringContainsString('<td>Produção</td>', $formatted);
        $this->assertStringNotContainsString('Array', $formatted);
    }
}
