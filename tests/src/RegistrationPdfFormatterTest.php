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
}
