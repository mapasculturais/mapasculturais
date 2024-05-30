<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class SpaceTestFixtures extends AbstractTestFixtures implements TestFixtures
{
    public static function partial(): self
    {
        return new self([
            'location' => [
                'latitude' => '10',
                'longitude' => '10',
            ],
            'name' => 'Espaço da Cultura',
            'public' => true,
            'shortDescription' => 'Um ponto de encontro para compartilhar cultura.',
            'longDescription' => 'Portal que nos transporta para o coração pulsante da rica cultura do brasileiro.',
            'emailPublico' => 'test@test.com',
            'emailPrivado' => 'test@test.com',
            'cnpj' => '00.000.000/0000-00',
            'razaoSocial' => 'Centro Cultural Test',
            'telefonePublico' => '0000-0000',
            'telefone1' => '0000-0000',
            'telefone2' => '0000-0000',
            'acessibilidade' => 'Sim',
            'acessibilidade_fisica' => ['Banheiros adaptados', 'Bebedouro adaptado', 'Circuito de visitação adaptado', 'Rampa de acesso', 'Sanitário adaptado'],
            'capacidade' => 20,
            'endereco' => 'Test',
            'En_CEP' => '80000',
            'En_Nome_Logradouro' => 'Rua test',
            'En_Num' => '1234',
            'En_Complemento' => 'Test',
            'En_Bairro' => 'Test',
            'En_Municipio' => 'Test',
            'En_Estado' => 'CE',
            'horario' => '7:00 - 22:00',
        ]);
    }

    public static function complete(): array
    {
        return [];
    }
}
