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

    public function testFormatsLocationFieldObjectAsReadableLines(): void
    {
        $config = (object) [
            'fieldType' => 'agent-owner-field',
            'config' => (object) [
                'entityField' => '@location',
            ],
        ];

        $value = (object) [
            'address_postalCode' => '79074055',
            'address_level0' => 'BR',
            'address_level1' => null,
            'address_level2' => 'MS',
            'address_level3' => null,
            'address_level4' => 'Campo Grande',
            'address_level5' => null,
            'address_level6' => 'Jardim Monte Alegre',
            'address_line1' => 'Rua Cefalândia, 15',
            'address_line2' => 'Teste teste',
            'endereco' => 'Rua Cefalândia, 15, Teste teste - Jardim Monte Alegre - Campo Grande/MS - CEP: 79074-055',
            'location' => (object) [
                'latitude' => '0',
                'longitude' => '0',
            ],
            'publicLocation' => true,
            'En_Pais' => 'BR',
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, $value);

        $this->assertSame(
            implode("\n", [
                'Código postal: 79074-055',
                'País: BR',
                'Estado/Província: MS',
                'Município/Cidade/Comune: Campo Grande',
                'Bairro: Jardim Monte Alegre',
                'Endereço: Rua Cefalândia, 15',
                'Complemento: Teste teste',
                'endereco: Rua Cefalândia, 15, Teste teste - Jardim Monte Alegre - Campo Grande/MS - CEP: 79074-055',
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

    public function testFormatsAddressesFieldAsHtmlList(): void
    {
        $config = (object) [
            'fieldType' => 'addresses',
        ];

        $value = [
            [
                'logradouro' => 'Rua das Flores',
                'numero' => '123',
                'bairro' => 'Centro',
                'cidade' => 'Campo Grande',
                'estado' => 'MS',
                'cep' => '79000-000',
                'complemento' => 'Sala <script>',
            ],
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValueAsHtml($config, $value);

        $this->assertStringContainsString('<ul class="address-list">', $formatted);
        $this->assertStringContainsString('<li>Rua das Flores, nº 123 - Centro, Campo Grande/MS, 79000-000 (Sala &lt;script&gt;)</li>', $formatted);
        $this->assertStringNotContainsString('Array', $formatted);
        $this->assertStringNotContainsString('<script>', $formatted);
    }

    public function testFormatsCheckboxesArrayOfStrings(): void
    {
        $config = (object) [
            'fieldType' => 'checkboxes',
        ];

        $value = [
            'Audiovisual e cinema',
            'Social Media, Comunicação digital, redes e estratégia',
            'Música, som e produção sonora',
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, $value);

        $this->assertSame(
            'Audiovisual e cinema, Social Media, Comunicação digital, redes e estratégia, Música, som e produção sonora',
            $formatted
        );
    }

    public function testFormatsScalarStringValue(): void
    {
        $config = (object) [
            'fieldType' => 'select',
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, 'Sim');

        $this->assertSame('Sim', $formatted);
    }

    public function testFormatsLinksArrayOfObjectsWithoutThrowing(): void
    {
        $config = (object) [
            'fieldType' => 'agent-owner-field',
            'config' => [
                'entityField' => '@links',
            ],
        ];

        $value = [
            (object) [
                'title' => '2º lugar Mister Trans Brasil 2022',
                'value' => 'https://exemplo.org/noticia',
            ],
            (object) [
                'title' => 'DJ Parada do Orgulho LGBT',
                'value' => 'https://exemplo.org/evento',
            ],
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, $value);
        $formattedHtml = RegistrationPdfFormatter::formatFieldValueAsHtml($config, $value);

        $this->assertStringContainsString('https://exemplo.org/noticia', $formatted);
        $this->assertStringContainsString('https://exemplo.org/evento', $formatted);
        $this->assertStringContainsString('Mister Trans', $formatted);
        $this->assertStringNotContainsString('Array', $formatted);
        $this->assertStringContainsString('https://exemplo.org/noticia', $formattedHtml);
    }

    public function testFormatsAgentFilesArrayOfObjectsWithoutThrowing(): void
    {
        $config = (object) [
            'fieldType' => 'agent-owner-field',
            'config' => [
                'entityField' => 'files',
            ],
        ];

        $value = [
            (object) [
                'id' => 145903,
                'name' => 'portfolio.pdf',
                'url' => 'https://exemplo.org/files/portfolio.pdf',
            ],
        ];

        $formatted = RegistrationPdfFormatter::formatFieldValue($config, $value);

        $this->assertStringContainsString('portfolio.pdf', $formatted);
        $this->assertStringContainsString('https://exemplo.org/files/portfolio.pdf', $formatted);
        $this->assertStringNotContainsString('Array', $formatted);
    }
}
