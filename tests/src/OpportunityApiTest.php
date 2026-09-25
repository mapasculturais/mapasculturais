<?php

namespace Test;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

class OpportunityApiTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        RegistrationDirector,
        UserDirector,
        SpaceDirector;


    protected function getArraySlice(array $array, int $page, int $items_per_page) {
        $offset = ($page - 1) * $items_per_page;

        return array_slice($array, $offset, $items_per_page);
    }

    function testPagination()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $space = $this->spaceDirector->createSpace($admin->profile);
        $opportunities = [];
        $names = [
            1 => 'Ana',
            2 => 'Beatriz',
            3 => 'Carolina',
            4 => 'Daniela',
            5 => 'Eduarda',
            6 => 'Fabi',
            7 => 'Gabriela',
            8 => 'Helena',
            9 => 'Iara',
            10 => 'Juliana',
            11 => 'Kelly',
            12 => 'Laura',
            13 => 'Maria'
        ];

        for($i = 1; $i <= 13; $i++) {
            $owner_entity = $i % 2 === 0 ? $admin->profile : $space;

            $opportunities[] = $this->opportunityBuilder
                ->reset(owner: $admin->profile, owner_entity: $owner_entity)
                ->fillRequiredProperties()
                ->setName($names[$i])
                ->firstPhase()
                    ->setRegistrationPeriod(new Open)
                    ->done()

                ->save()
                ->getInstance();
        }
        
        usort($opportunities, function($o1, $o2) {
            $result = strtolower($o1->name) <=> strtolower($o2->name);
            
            if($result) {
                return $result;
            }

            return $o1->id <=> $o2->id;
        });
        
        $query = new ApiQuery(Opportunity::class, [
            '@select' => 'id,name',
        ]);

        $this->assertEquals(count($opportunities), $query->count(), 'Certificando que a contagem de oportunidades é igual de oportunidades criadas');

        $limit = 3;

        for($page = 1; $page <= 6; $page++) {
            $page_opportunities = $this->getArraySlice($opportunities, $page, $limit);

            // Teste pelo find
            $query = new ApiQuery(Opportunity::class, [
                '@select' => 'id,name,ownerEntity.{name}',
                '@order' => 'name ASC',
                '@limit' => $limit,
                '@page' => $page
            ]);
            $result = $query->find();

            $this->assertCount(count($page_opportunities), $result, "[find] Certificando que a contagem está correta na página {$page}");

            foreach($page_opportunities as $i => $opportunity) {
                $this->assertEquals($opportunity->id, $result[$i]['id'], "[find] Certificando que na página {$page} a oportunidade de posição {$i} tem o valor correto");
            }

            // Teste pelo findIds
            $query = new ApiQuery(Opportunity::class, [
                '@select' => 'id,name,ownerEntity.{name}',
                '@order' => 'name ASC',
                '@limit' => $limit,
                '@page' => $page
            ]);
            $result = $query->findIds();

            $this->assertCount(count($page_opportunities), $result, "[findIds] Certificando que a contagem está correta na página {$page}");

            foreach($page_opportunities as $i => $opportunity) {
                $this->assertEquals($opportunity->id, $result[$i], "[findIds] Certificando que na página {$page} a oportunidade de posição {$i} tem o valor correto");
            }
        }
    }

    function testDuplicateOpportunity()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setCategories(['Categoria A', 'Categoria B'])
            ->setProponentTypes(['Pessoa Física', 'Pessoa Jurídica'])
            ->setRanges([
                ['label' => 'Faixa 1', 'limit' => 10, 'value' => 1]
            ])
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa principal')
                ->createField('cor', 'select', title: 'Campo Cor', required: true, options: ['Azul', 'Vermelho'])
                ->createField('resumo', 'text', title: 'Campo Resumo', required: false)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $opportunity->registrationCategTitle = 'Categorias de teste';
        $opportunity->save(true);

        $latest_before = $this->app->repo('Opportunity')->findBy([], ['id' => 'DESC'], 1);
        $max_id_before = $latest_before ? $latest_before[0]->id : 0;

        $request = $this->requestFactory->POST(
            controller_id: 'opportunity',
            action: 'duplicate',
            url_params: [$opportunity->id],
            ajax: true
        );
        $this->assertStatus200($request, 'Garantindo status 200 ao duplicar oportunidade');

        $this->app->em->clear();

        $opportunities = $this->app->repo('Opportunity')->findBy([], ['id' => 'DESC']);

        /** @var Opportunity */
        $duplicated = null;
        foreach ($opportunities as $item) {
            if ($item->id > $max_id_before && str_contains((string) $item->name, '[Cópia]')) {
                $duplicated = $item;
                break;
            }
        }

        $this->assertNotNull($duplicated, 'Certificando que a oportunidade duplicada foi encontrada');
        $this->assertEquals(Opportunity::STATUS_DRAFT, $duplicated->status, 'Certificando que a cópia inicia como rascunho');
        $this->assertEquals($admin->profile->id, $duplicated->owner->id, 'Certificando que o dono da cópia é o usuário autenticado');
        $this->assertStringContainsString($opportunity->name, $duplicated->name, 'Certificando que o nome original é preservado no nome da cópia');
        $this->assertStringContainsString('[Cópia]', $duplicated->name, 'Certificando que o sufixo de cópia é adicionado no nome');
        $this->assertEquals($opportunity->registrationCategories, $duplicated->registrationCategories, 'Certificando que categorias de inscrição foram duplicadas');
        
        $duplicated_field_titles = array_map(
            fn($field) => $field->title,
            $duplicated->getRegistrationFieldConfigurations()
        );

        $this->assertContains('Campo Cor', $duplicated_field_titles, 'Certificando que o campo "Campo Cor" foi duplicado');
        $this->assertContains('Campo Resumo', $duplicated_field_titles, 'Certificando que o campo "Campo Resumo" foi duplicado');
    }
}
