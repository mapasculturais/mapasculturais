<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Space;

class SpaceFixtures extends Fixture implements DependentFixtureInterface
{
    public const SPACE_ID_PREFIX = 'space';
    public const SPACE_ID_1 = 1;
    public const SPACE_ID_2 = 2;
    public const SPACE_ID_3 = 3;

    public const SPACES = [
        [
            'id' => self::SPACE_ID_1,
            'name' => 'Secretaria da Cultura do Estado do Ceará - SECULT',
            'shortDescription' => 'A Secretaria da Cultura do Estado do Ceará (Secult) foi criada pela Lei nº 8.541, de 9 de agosto de 1966, durante o governo de Virgílio Távora. A Secult tem como missão executar, superintender e coordenar as atividades de proteção do patrimônio cultural do Ceará, difusão da cultura e aprimoramento cultural do povo cearense.',
            'longDescription' => 'A Secretaria da Cultura do Estado do Ceará (Secult) foi criada pela Lei nº 8.541, de 9 de agosto de 1966, durante o governo de Virgílio Távora, sendo a primeira secretaria da Cultura criada no Brasil. Localizada atualmente no prédio do Cineteatro São Luiz, no Centro de Fortaleza (Rua Major Facundo, nº500), a Secult nasceu com o objetivo atender aos anseios culturais do povo cearense, propiciando maior desenvolvimento a todas as manifestações de cultura e valorizando a tradição de seu povo. Este pioneirismo na área cultural representa, em si, mais uma comprovação da tenacidade do cearense. Logo após sua criação, as realizações foram tantas que conseguiram ultrapassar as fronteiras do Ceará e ressonaram em todos os meios culturais do País. A Secult tem como missão executar, superintender e coordenar as atividades de proteção do patrimônio cultural do Ceará, difusão da cultura e aprimoramento cultural do povo cearense.',
            'status' => 1,
            'type' => '40',
            '_ownerId' => 1,
            'terms' => [
                'area' => ['Gestão Cultural'],
            ],
            'emailPublico' => 'pub@secult.br',
            'emailPrivado' => 'piv@secult.br',
            'cnpj' => '07.954.555/0001-11',
            'razaoSocial' => 'Secretaria da Cultura do Estado do Ceará - SECULT',
            'telefonePublico' => '0000-0000',
            'telefone1' => '0000-0000',
            'telefone2' => '0000-0000',
            'acessibilidade' => 'Sim',
            'acessibilidade_fisica' => ['Rampa de acesso'],
            'capacidade' => 100,
            'endereco' => 'R. Major Facundo, 500, Ao lado da praça - Centro - Fortaleza/CE - CEP: 60025100',
            'En_CEP' => '60025100',
            'En_Nome_Logradouro' => 'R. Major Facundo',
            'En_Num' => '500',
            'En_Complemento' => 'Ao lado da praça',
            'En_Bairro' => 'Centro',
            'En_Municipio' => 'Fortaleza',
            'En_Estado' => 'CE',
            'horario' => 'De segunda a sexta-feira, de 8h às 17h.',
        ],
        [
            'id' => self::SPACE_ID_2,
            'name' => 'Biblioteca Municipal Pedro Maia Rocha',
            'shortDescription' => 'Espaço público destinado à leitura no município de Russas.',
            'longDescription' => 'Espaço público destinado à leitura em geral, bem como aos aspectos históricos e culturais do município de Russas.',
            'status' => 1,
            'type' => '20',
            '_ownerId' => 1,
            'terms' => [
                'area' => ['Leitura', 'Livro'],
            ],
            'emailPublico' => 'biblioteca.pub.pedro.maia.rocha@russas.br',
            'emailPrivado' => 'biblioteca.piv.pedro.maia.rocha@russas.br',
            'cnpj' => '07.535.446/0001-60',
            'razaoSocial' => 'Biblioteca Municipal Pedro Maia Rocha',
            'telefonePublico' => '0000-0000',
            'telefone1' => '0000-0000',
            'telefone2' => '0000-0000',
            'acessibilidade' => 'Sim',
            'acessibilidade_fisica' => ['Rampa de acesso'],
            'capacidade' => 50,
            'endereco' => 'Av. Dom Lino, 1320, Na avenida principal - Centro - Russas/CE - CEP: 62900000',
            'En_CEP' => '62900000',
            'En_Nome_Logradouro' => 'Av. Dom Lino',
            'En_Num' => '1320',
            'En_Complemento' => 'Na avenida principal',
            'En_Bairro' => 'Centro',
            'En_Municipio' => 'Russas',
            'En_Estado' => 'CE',
            'horario' => '7:00 - 19:00',
        ],
        [
            'id' => self::SPACE_ID_3,
            'name' => 'Centro Cultural Carnaubeira',
            'shortDescription' => 'Espaço com 960m², tem teatro multiuso com palco em tablado, com capacidade para 170 pessoas, onde pode ser realizados eventos de teatro, cinema, musica etc. conta ainda com 02 salas de aulas para oficina de artes, alem de espaço administrativo. O CCC é um espaço da Associação Carnaubeira de Arte-educação e está em funcionamento desde 2010 mesmo não estando completamente acabado.',
            'longDescription' => 'Espaço com 960m², tem teatro multiuso com palco em tablado, com capacidade para 170 pessoas, onde pode ser realizados eventos de teatro, cinema, musica etc. conta ainda com 02 salas de aulas para oficina de artes, alem de espaço administrativo. O CCC é um espaço da Associação Carnaubeira de Arte-educação e está em funcionamento desde 2010 mesmo não estando completamente acabado.',
            'status' => 1,
            'type' => '41',
            '_ownerId' => 1,
            'terms' => [
                'area' => ['Artes Visuais', 'Audiovisual', 'Cinema', 'Música', 'Produção Cultural', 'Teatro'],
            ],
            'emailPublico' => 'pub.somdascarnaubeiras@gmail.com',
            'emailPrivado' => 'piv.somdascarnaubeiras@gmail.com',
            'cnpj' => '05.728.440/0001-83',
            'razaoSocial' => 'Centro Cultural Carnaubeira',
            'telefonePublico' => '0000-0000',
            'telefone1' => '0000-0000',
            'telefone2' => '0000-0000',
            'acessibilidade' => 'Sim',
            'acessibilidade_fisica' => ['Banheiros adaptados', 'Bebedouro adaptado', 'Circuito de visitação adaptado', 'Rampa de acesso', 'Sanitário adaptado'],
            'capacidade' => 20,
            'endereco' => 'Rua José Alves, 1719, Flores - Russas/CE - CEP: 62903000',
            'En_CEP' => '62903000',
            'En_Nome_Logradouro' => 'Rua José Alves',
            'En_Num' => '1719',
            'En_Complemento' => 'Flores',
            'En_Bairro' => 'Flores',
            'En_Municipio' => 'Russas',
            'En_Estado' => 'CE',
            'horario' => '7:00 - 22:00',
        ],
    ];

    public function getDependencies(): array
    {
        return [
            TermFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->deleteAllDataFromTable(Space::class);

        $user = $this->getReference(UserFixtures::USER_ID_PREFIX.'-'.UserFixtures::USER_ID_1);

        foreach (self::SPACES as $spaceData) {
            $space = $this->getSerializer()->denormalize($spaceData, Space::class);
            $this->setProperty($space, 'owner', $user);
            $this->setReference(sprintf('%s-%s', self::SPACE_ID_PREFIX, $spaceData['id']), $space);

            $manager->persist($space);
        }

        $manager->flush();
    }
}
