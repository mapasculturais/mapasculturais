<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Past;
use Tests\Directors\QuotaRegistrationDirector;
use Tests\Enums\EvaluationMethods;
use Tests\Enums\ProponentTypes;
use Tests\Fixtures;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationMethodTechnicalTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    protected QuotaRegistrationDirector $quotaRegistrationDirector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quotaRegistrationDirector = new QuotaRegistrationDirector($this->opportunityBuilder, $this->registrationDirector);
    }


    function testQuotaInFirstEvaluationPhase() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity_vacancies = 12;
        $quota_vacancies = 3;

        /*
         * O arquivo está preparado de tal maneira que quando a oportunidade estiver configurada
         * para "considerar os cotistas dentro da lista da ampla concorrência", o número 
         * de inscrições 'elegíveis' às cotas na zona de classificação seja 3;
         * 
         * Já quando a oportunidade estiver configurada para "NÃO considerar os cotistas
         * dentro da lista da ampla concorrência", o número de inscrições 'elegíveis' às 
         * cotas na zona de classificação (12) será 4.
         * 
         * Em ambas as situações a quantidade de cotistas (usingQuota) será 3
         */
        $source = Fixtures::getCSV('registration-list-for-quota.csv');

        /** @var Opportunity */
        $this->opportunityBuilder
                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                ->fillRequiredProperties()
                ->setVacancies($opportunity_vacancies)
                ->addProponentType(ProponentTypes::PESSOA_FISICA)
                ->addProponentType(ProponentTypes::COLETIVO)
                ->addProponentType(ProponentTypes::PESSOA_JURIDICA)
                ->save()
                ->firstPhase()
                    ->setRegistrationPeriod(new Past)
                    ->enableQuotaQuestion()
                    ->createStep('Informações')
                    ->createOwnerField(
                            identifier: 'data-nascimento',
                            entity_field: 'dataDeNascimento',
                            title: 'Data de Nascimento',
                            required: true)
                    ->createOwnerField(
                            identifier: 'raca', 
                            entity_field: 'raca', 
                            title: 'Raça', 
                            required: true)
                    ->createField(
                            identifier:'maioria-negra',
                            field_type: 'select',
                            title: 'Maioria da sociedade é negra',
                            required: true,
                            proponent_types: [ProponentTypes::PESSOA_JURIDICA],
                            options: ['Sim', 'Não'])
                    ->done()
                ->save();

        $evaluation_phase_builder = $this->opportunityBuilder->addEvaluationPhase(EvaluationMethods::technical);

        $quota_builder = $evaluation_phase_builder
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->addValuers(2, 'committee 1')
                ->config()
                    ->quota()
                        ->setConsiderQuotasInGeneralList(false)
                        ->addRule('Pessoas Negras', vacancies: $quota_vacancies)
                        ->addRuleField('raca', ['Preta', 'Parda'], ProponentTypes::PESSOA_FISICA)
                        ->addRuleField('raca', ['Preta', 'Parda'], ProponentTypes::COLETIVO)
                        ->addRuleField('maioria-negra', ['Sim'], ProponentTypes::PESSOA_JURIDICA);
                        
        $opportunity = $this->opportunityBuilder
                ->save()
                ->refresh()
                ->getInstance();

        $registrations = [];

        $field_raca = $this->opportunityBuilder->getFieldName('raca');
        $field_maioria_negra = $this->opportunityBuilder->getFieldName('maioria-negra');
        $field_data_nascimento = $this->opportunityBuilder->getFieldName('data-nascimento');
        
        foreach($source as $i => $registration_data) {
            $registration_data[$field_raca] = $registration_data['raca'];
            $registration_data[$field_maioria_negra] = $registration_data['maioria-negra'];
            $registration_data[$field_data_nascimento] = $registration_data['data-nascimento'];

            $registration = $this->registrationDirector->createSentRegistration($opportunity, $registration_data);

            $registrations[] = $registration;
        }

        $app = App::i();

        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');



        /**
         * --------------- 
         * TESTANDO COM A OPÇÃO "considerar os cotistas dentro da lista da ampla concorrência" DESATIVADA
         */
        $quota_builder->setConsiderQuotasInGeneralList(false);
        $opportunity->save(true);

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "proponentType,$field_raca,$field_maioria_negra,$field_data_nascimento,score,eligible,appliedForQuota,usingQuota,quotas",
            '@order' => '@quota'
        ], true);

        $classification_zone = array_slice($query_result->registrations, 0, $opportunity_vacancies);

        $quotists = array_filter($classification_zone, fn($registration) => !!$registration['usingQuota']);
        $this->assertCount($quota_vacancies, $quotists, "Certificando que o número de cotistas classificados está correto");

        $eligible = array_filter($classification_zone, fn($registration) => !!$registration['eligible']);
        $this->assertCount(4, $eligible, "Certificando que o número de inscrições elegíveis a cota dentro da zona de classificação está correto");





        /**
         * --------------- 
         * TESTANDO COM A OPÇÃO "considerar os cotistas dentro da lista da ampla concorrência" ATIVADA
         */
        $quota_builder->setConsiderQuotasInGeneralList(true);
        $opportunity->save(true);

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "proponentType,$field_raca,$field_maioria_negra,$field_data_nascimento,score,eligible,appliedForQuota,usingQuota,quotas",
            '@order' => '@quota'
        ], true);

        $classification_zone = array_slice($query_result->registrations, 0, $opportunity_vacancies);

        $quotists = array_filter($classification_zone, fn($registration) => !!$registration['usingQuota']);
        $this->assertCount($quota_vacancies, $quotists, "Certificando que o número de cotistas classificados está correto");

        $eligible = array_filter($classification_zone, fn($registration) => !!$registration['eligible']);
        $this->assertCount(3, $eligible, "Certificando que o número de inscrições elegíveis a cota dentro da zona de classificação está correto");
    }

    protected function createOpportunityWithRanges(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->addRange('Longa Metragem', 3, 0)
            ->addRange('Curta Metragem', 7, 0)
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->done()
            ->save()
            ->getInstance();

        return $opportunity;
    }

    protected function createOpportunityWithQuotas(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(15)
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Etapa 1')
                ->createOwnerField('raca', 'raca', 'Raça/Cor', required: false)
                ->createOwnerField('pessoaDeficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Pessoas Negras', 3)
                            ->addRuleField('raca', ['Preta', 'Parda'])
                        ->addRule('Indígenas', 1)
                            ->addRuleField('raca', ['Indígena'])
                        ->addRule('PCD', 1)
                            ->addRuleField('pessoaDeficiente', ['Auditiva', 'Física-motora', 'Intelectual', 'Múltipla', 'Transtorno do Espectro Autista', 'Visual', 'Outras'])
                    ->done() 
                ->done()      
                ->done()      
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    protected function createFictitiousGeoDivisions(): array
    {
        $app = App::i();
        $conn = $app->conn;
        
        $regions = [
            [
                'type' => '',
                'cod' => '',
                'name' => QuotaRegistrationDirector::REGION_CAPITAL,
            ],
            [
                'type' => '',
                'cod' => '',
                'name' => QuotaRegistrationDirector::REGION_COASTAL,
            ],
            [
                'type' => '',
                'cod' => '',
                'name' => QuotaRegistrationDirector::REGION_INTERIOR,
            ],
        ];
        
        $created_regions = [];
        
        foreach ($regions as $region) {
            /** @var \Doctrine\DBAL\Connection */
            $conn = $app->conn;
            $conn->executeQuery(
                "INSERT INTO geo_division (parent_id, type, cod, name, geom) 
                 VALUES (NULL, :type, :cod, :name, NULL)",
                [
                    'type' => $region['type'],
                    'cod' => $region['cod'],
                    'name' => $region['name'],
                ]
            );
            
            $created_regions[$region['name']] = $region['cod'];
        }
        
        return $created_regions;
    }

    protected function createOpportunityWithTerritoryVacancies(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(60)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->createStep('Informações')
                ->createField(
                    identifier: 'regiao',
                    field_type: 'select',
                    title: 'Região',
                    required: false,
                    options: [
                        QuotaRegistrationDirector::REGION_CAPITAL,
                        QuotaRegistrationDirector::REGION_COASTAL,
                        QuotaRegistrationDirector::REGION_INTERIOR
                    ]
                )
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->geoQuota()
                        ->setGeoDivision('field')  // Valor fictício
                        ->setField('regiao')
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_CAPITAL, 30)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_COASTAL, 18)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_INTERIOR, 12)
                    ->done()
                ->done()
            ->done()
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    protected function createOpportunityWithQuotasAndTerritoryVacancies(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(100)
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Etapa 1')
                ->createOwnerField('raca', 'raca', 'Raça/Cor', required: false)
                ->createOwnerField('pessoaDeficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                ->createField(
                    identifier: 'regiao',
                    field_type: 'select',
                    title: 'Região',
                    required: false,
                    options: [
                        QuotaRegistrationDirector::REGION_CAPITAL,
                        QuotaRegistrationDirector::REGION_COASTAL,
                        QuotaRegistrationDirector::REGION_INTERIOR
                    ]
                )
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Pessoas Negras', 20)
                            ->addRuleField('raca', ['Preta', 'Parda'])
                        ->addRule('Indígenas', 5)
                            ->addRuleField('raca', ['Indígena'])
                        ->addRule('PCD', 2)
                            ->addRuleField('pessoaDeficiente', ['Auditiva', 'Física-motora', 'Intelectual', 'Múltipla', 'Transtorno do Espectro Autista', 'Visual', 'Outras'])
                    ->done()
                    ->geoQuota()
                        ->setGeoDivision('field')  // Valor fictício
                        ->setField('regiao')
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_CAPITAL, 50)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_COASTAL, 30)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_INTERIOR, 20)
                    ->done()
                ->done()      
                ->done()      
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    protected function createOpportunityWithRangesAndQuotas(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->addRange('Longa Metragem', 3, 0)
            ->addRange('Curta Metragem', 7, 0)
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Etapa 1')
                ->createOwnerField('raca', 'raca', 'Raça/Cor', required: false)
                ->createOwnerField('pessoaDeficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Pessoas Negras', 2)
                            ->addRuleField('raca', ['Preta', 'Parda'])
                        ->addRule('Indígenas', 1)
                            ->addRuleField('raca', ['Indígena'])
                        ->addRule('PCD', 1)
                            ->addRuleField('pessoaDeficiente', ['Auditiva', 'Física-motora', 'Intelectual', 'Múltipla', 'Transtorno do Espectro Autista', 'Visual', 'Outras'])
                    ->done() 
                ->done()      
                ->done()      
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    protected function createOpportunityWithRangesAndTerritoryVacancies(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->addRange('Longa Metragem', 3, 0)
            ->addRange('Curta Metragem', 7, 0)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->createStep('Informações')
                ->createField(
                    identifier: 'regiao',
                    field_type: 'select',
                    title: 'Região',
                    required: false,
                    options: [
                        QuotaRegistrationDirector::REGION_CAPITAL,
                        QuotaRegistrationDirector::REGION_COASTAL,
                        QuotaRegistrationDirector::REGION_INTERIOR
                    ]
                )
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->geoQuota()
                        ->setGeoDivision('field')  // Valor fictício
                        ->setField('regiao')
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_CAPITAL, 5)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_COASTAL, 3)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_INTERIOR, 2)
                    ->done()
                ->done()
            ->done()
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    protected function createOpportunityWithRangesQuotasAndTerritoryVacancies(User $admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(100)
            ->addRange('Longa Metragem', 30, 0)
            ->addRange('Curta Metragem', 70, 0)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Informações')
                ->createOwnerField('raca', 'raca', 'Raça/Cor', required: false)
                ->createOwnerField('pessoaDeficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                ->createField(
                    identifier: 'regiao',
                    field_type: 'select',
                    title: 'Região',
                    required: false,
                    options: [
                        QuotaRegistrationDirector::REGION_CAPITAL,
                        QuotaRegistrationDirector::REGION_COASTAL,
                        QuotaRegistrationDirector::REGION_INTERIOR
                    ]
                )
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Pessoas Negras', 20)
                            ->addRuleField('raca', ['Preta', 'Parda'])
                        ->addRule('Indígenas', 5)
                            ->addRuleField('raca', ['Indígena'])
                        ->addRule('PCD', 2)
                            ->addRuleField('pessoaDeficiente', ['Auditiva', 'Física-motora', 'Intelectual', 'Múltipla', 'Transtorno do Espectro Autista', 'Visual', 'Outras'])
                    ->geoQuota()
                        ->setGeoDivision('field')
                        ->setField('regiao')
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_CAPITAL, 50)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_COASTAL, 30)
                        ->addRegionDistribution(QuotaRegistrationDirector::REGION_INTERIOR, 20)
                    ->done()
                ->done()
            ->done()
            ->save()
            ->refresh()
            ->getInstance();

        return $opportunity;
    }

    function testRangeClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRanges($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealRangesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity'); 

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota'
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (3 Longas + 7 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($i = 0; $i < 10; $i++) {
            $registration = $query_result->registrations[$i];

            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (3 Longas + 7 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "[LIMIT 10] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "[LIMIT 10] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[LIMIT 10] A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => 'number,range,score,eligible',
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                }
    
                if ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }
            }
        }      

        $this->assertEquals(3, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= 40 (nota de corte)");

    }

    function testRangeClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRanges($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedRangesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota'
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (máx. 3 Longas + 7 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($i = 0; $i < 10; $i++) {
            $registration = $query_result->registrations[$i];

            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados 9 Curtas (7 da faixa + 2 que preenchem vagas de Longa não preenchidas)
        $this->assertEquals(9, $curta_count, "Deve ter 9 inscrições de Curta Metragem classificadas (7 da faixa + 2 preenchendo vagas de Longa)");

        // Verifica que foram selecionados apenas 1 Longa (não 3, pois faltam candidatos qualificados)
        $this->assertEquals(1, $longa_count, "Deve ter apenas 1 inscrição de Longa Metragem classificada (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (máx. 3 Longas + 7 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados 9 Curtas (7 da faixa + 2 que preenchem vagas de Longa não preenchidas)
        $this->assertEquals(9, $curta_count, "[LIMIT 10] Deve ter 9 inscrições de Curta Metragem classificadas (7 da faixa + 2 preenchendo vagas de Longa)");

        // Verifica que foram selecionados apenas 1 Longa (não 3, pois faltam candidatos qualificados)
        $this->assertEquals(1, $longa_count, "[LIMIT 10] Deve ter apenas 1 inscrição de Longa Metragem classificada (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[LIMIT 10] A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => 'number,range,score,eligible',
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                }
    
                if ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }
            }
        }      

        $this->assertEquals(9, $curta_count, "[PAGINAÇÃO] Deve ter 9 inscrições de Curta Metragem classificadas (7 da faixa + 2 preenchendo vagas de Longa)");
        $this->assertEquals(1, $longa_count, "[PAGINAÇÃO] Deve ter apenas 1 inscrição de Longa Metragem classificada (faltam candidatos qualificados)");
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= 40 (nota de corte)");
    }

    function testQuotaClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithQuotas($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealQuotasScenario($opportunity);
 
        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
        ], true);
        
        // Conta as inscrições classificadas respeitando os limites de cada cota
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;
        $total_vacancies = 15;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Verifica se é cotista e conta por tipo de cota
            $quotas = $registration['quotas'] ?? [];
            $is_quota = !empty($quotas);
            
            if ($is_quota) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                } 
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            } else {
                $ampla_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Negras
        $this->assertEquals(3, $negra_count, "Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");

        // Verifica que foram selecionados exatamente 1 Indígena
        $this->assertEquals(1, $indigena_count, "Deve ter exatamente 1 inscrição de Indígenas classificada");

        // Verifica que foram selecionados exatamente 1 PCD
        $this->assertEquals(1, $pcd_count, "Deve ter exatamente 1 inscrição de PCD classificada");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 15,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Verifica se é cotista e conta por tipo de cota
            $quotas = $registration['quotas'] ?? [];
            $is_quota = !empty($quotas);

            if ($is_quota) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                } elseif (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                } elseif (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            } else {
                $ampla_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Negras
        $this->assertEquals(3, $negra_count, "[LIMIT 15] Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");

        // Verifica que foram selecionados exatamente 1 Indígena
        $this->assertEquals(1, $indigena_count, "[LIMIT 15] Deve ter exatamente 1 inscrição de Indígenas classificada");

        // Verifica que foram selecionados exatamente 1 PCD
        $this->assertEquals(1, $pcd_count, "[LIMIT 15] Deve ter exatamente 1 inscrição de PCD classificada");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 15] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 4; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 4,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Verifica se é cotista e conta por tipo de cota
                $quotas = $registration['quotas'] ?? [];
                $is_quota = !empty($quotas);

                if ($is_quota) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    } elseif (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    } elseif (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                } else {
                    $ampla_count++;
                }
            }
        }      

        $this->assertEquals(3, $negra_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");
        $this->assertEquals(1, $indigena_count, "[PAGINAÇÃO] Deve ter exatamente 1 inscrição de Indígenas classificada");
        $this->assertEquals(1, $pcd_count, "[PAGINAÇÃO] Deve ter exatamente 1 inscrição de PCD classificada");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

    }

    function testQuotaClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithQuotas($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedQuotasScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota'
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;

        for($i = 0; $i < 15; $i++) {
            $registration = $query_result->registrations[$i];

            $lowest_score = min($lowest_score, $registration['score']);

            // Verifica se é cotista e conta por tipo de cota
            $quotas = $registration['quotas'] ?? [];
            $is_quota = !empty($quotas);

            if ($is_quota) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                } elseif (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                } elseif (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            } else {
                $ampla_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Negras
        $this->assertEquals(3, $negra_count, "Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");

        // Verifica que foram selecionados 0 Indígenas (não 1, pois faltam candidatos qualificados)
        $this->assertEquals(0, $indigena_count, "Deve ter 0 inscrições de Indígenas classificadas (faltam candidatos qualificados)");

        // Verifica que foram selecionados 0 PCD (não 1, pois faltam candidatos qualificados)
        $this->assertEquals(0, $pcd_count, "Deve ter 0 inscrições de PCD classificadas (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,raca,pessoaDeficiente,score,eligible,quotas',
            '@order' => '@quota',
            '@limit' => 15,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Verifica se é cotista e conta por tipo de cota
            $quotas = $registration['quotas'] ?? [];
            $is_quota = !empty($quotas);

            if ($is_quota) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                } elseif (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                } elseif (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            } else {
                $ampla_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Negras
        $this->assertEquals(3, $negra_count, "[LIMIT 15] Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");

        // Verifica que foram selecionados 0 Indígenas (não 1, pois faltam candidatos qualificados)
        $this->assertEquals(0, $indigena_count, "[LIMIT 15] Deve ter 0 inscrições de Indígenas classificadas (faltam candidatos qualificados)");

        // Verifica que foram selecionados 0 PCD (não 1, pois faltam candidatos qualificados)
        $this->assertEquals(0, $pcd_count, "[LIMIT 15] Deve ter 0 inscrições de PCD classificadas (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 15] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $ampla_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 4; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 4,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Verifica se é cotista e conta por tipo de cota
                $quotas = $registration['quotas'] ?? [];
                $is_quota = !empty($quotas);

                if ($is_quota) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    } elseif (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    } elseif (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                } else {
                    $ampla_count++;
                }
            }
        }      

        $this->assertEquals(3, $negra_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Pessoas Negras classificadas");
        $this->assertEquals(0, $indigena_count, "[PAGINAÇÃO] Deve ter 0 inscrições de Indígenas classificadas (faltam candidatos qualificados)");
        $this->assertEquals(0, $pcd_count, "[PAGINAÇÃO] Deve ter 0 inscrições de PCD classificadas (faltam candidatos qualificados)");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

    }

    function testTerritoryVacanciesClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithTerritoryVacancies($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém o nome do campo regiao
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_regiao},score,eligible",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 60;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Busca a região do campo da inscrição
            $region = $registration[$field_regiao] ?? null;
            
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 30 da Capital
        $this->assertEquals(30, $capital_count, "Deve ter exatamente 30 inscrições da Região da Capital classificadas");

        // Verifica que foram selecionados exatamente 18 do Litoral
        $this->assertEquals(18, $coastal_count, "Deve ter exatamente 18 inscrições da Região Litorânea classificadas");

        // Verifica que foram selecionados exatamente 12 do Interior
        $this->assertEquals(12, $interior_count, "Deve ter exatamente 12 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_regiao},score,eligible",
            '@order' => '@quota',
            '@limit' => 60,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada região
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Busca a região do campo da inscrição
            $region = $registration[$field_regiao] ?? null;
            
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 30 da Capital
        $this->assertEquals(30, $capital_count, "[LIMIT 60] Deve ter exatamente 30 inscrições da Região da Capital classificadas");

        // Verifica que foram selecionados exatamente 18 do Litoral
        $this->assertEquals(18, $coastal_count, "[LIMIT 60] Deve ter exatamente 18 inscrições da Região Litorânea classificadas");

        // Verifica que foram selecionados exatamente 12 do Interior
        $this->assertEquals(12, $interior_count, "[LIMIT 60] Deve ter exatamente 12 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 60] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 6; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_regiao},score,eligible",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Busca a região do campo da inscrição
                $region = $registration[$field_regiao] ?? null;
                
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }      

        $this->assertEquals(30, $capital_count, "[PAGINAÇÃO] Deve ter exatamente 30 inscrições da Região da Capital classificadas");
        $this->assertEquals(18, $coastal_count, "[PAGINAÇÃO] Deve ter exatamente 18 inscrições da Região Litorânea classificadas");
        $this->assertEquals(12, $interior_count, "[PAGINAÇÃO] Deve ter exatamente 12 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testTerritoryVacanciesClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithTerritoryVacancies($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém o nome do campo regiao
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_regiao},score,eligible",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 60;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Busca a região do campo da inscrição
            $region = $registration[$field_regiao] ?? null;
            
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados 60 no total (mesmo com falta de candidatos do Interior)
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(60, $total_selected, "Deve ter exatamente 60 inscrições classificadas no total");

        // Verifica que foram selecionados apenas 3 do Interior (não 12, pois faltam candidatos qualificados)
        $this->assertEquals(3, $interior_count, "Deve ter apenas 3 inscrições da Região do Interior classificadas (faltam candidatos qualificados)");

        // Verifica que as vagas remanescentes do Interior foram distribuídas para Capital/Litoral
        // As 9 vagas restantes do Interior devem ter sido preenchidas por outras regiões
        $this->assertGreaterThan(30, $capital_count, "A Capital deve ter mais de 30 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(18, $coastal_count, "O Litoral deve ter mais de 18 inscrições (recebeu vagas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_regiao},score,eligible",
            '@order' => '@quota',
            '@limit' => 60,
        ], true);

        // Conta as inscrições classificadas
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Busca a região do campo da inscrição
            $region = $registration[$field_regiao] ?? null;
            
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados 60 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(60, $total_selected, "[LIMIT 60] Deve ter exatamente 60 inscrições classificadas no total");

        // Verifica que foram selecionados apenas 3 do Interior
        $this->assertEquals(3, $interior_count, "[LIMIT 60] Deve ter apenas 3 inscrições da Região do Interior classificadas (faltam candidatos qualificados)");

        // Verifica que as vagas remanescentes do Interior foram distribuídas
        $this->assertGreaterThan(30, $capital_count, "[LIMIT 60] A Capital deve ter mais de 30 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(18, $coastal_count, "[LIMIT 60] O Litoral deve ter mais de 18 inscrições (recebeu vagas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 60] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 6; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_regiao},score,eligible",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Busca a região do campo da inscrição
                $region = $registration[$field_regiao] ?? null;
                
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }      

        // Verifica que foram selecionados 60 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(60, $total_selected, "[PAGINAÇÃO] Deve ter exatamente 60 inscrições classificadas no total");

        // Verifica que foram selecionados apenas 3 do Interior
        $this->assertEquals(3, $interior_count, "[PAGINAÇÃO] Deve ter apenas 3 inscrições da Região do Interior classificadas (faltam candidatos qualificados)");

        // Verifica que as vagas remanescentes do Interior foram distribuídas
        $this->assertGreaterThan(30, $capital_count, "[PAGINAÇÃO] A Capital deve ter mais de 30 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(18, $coastal_count, "[PAGINAÇÃO] O Litoral deve ter mais de 18 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesAndQuotasClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesAndQuotas($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealRangesAndQuotasScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e cota
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;
        $total_vacancies = 10;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica que foram selecionadas pelo menos 2 Pessoas Negras (20% de 10)
        $this->assertGreaterThanOrEqual(2, $negra_count, "Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas (20% de 10)");

        // Verifica que foram selecionados pelo menos 1 Indígena (10% de 10)
        $this->assertGreaterThanOrEqual(1, $indigena_count, "Deve ter pelo menos 1 inscrição de Indígenas classificada (10% de 10)");

        // Verifica que foram selecionados pelo menos 1 PCD (10% de 10)
        $this->assertGreaterThanOrEqual(1, $pcd_count, "Deve ter pelo menos 1 inscrição de PCD classificada (10% de 10)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e cota
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "[LIMIT 10] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "[LIMIT 10] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica as cotas
        $this->assertGreaterThanOrEqual(2, $negra_count, "[LIMIT 10] Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(1, $indigena_count, "[LIMIT 10] Deve ter pelo menos 1 inscrição de Indígenas classificada");
        $this->assertGreaterThanOrEqual(1, $pcd_count, "[LIMIT 10] Deve ter pelo menos 1 inscrição de PCD classificada");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 10] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }
            }
        }

        $this->assertEquals(3, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(2, $negra_count, "[PAGINAÇÃO] Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(1, $indigena_count, "[PAGINAÇÃO] Deve ter pelo menos 1 inscrição de Indígenas classificada");
        $this->assertGreaterThanOrEqual(1, $pcd_count, "[PAGINAÇÃO] Deve ter pelo menos 1 inscrição de PCD classificada");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesAndQuotasClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesAndQuotas($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedRangesAndQuotasScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e cota
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;
        $total_vacancies = 10;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas (faixas não podem variar)
        $this->assertEquals(3, $longa_count, "Deve ter exatamente 3 inscrições de Longa Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionados exatamente 7 Curtas (faixas não podem variar)
        $this->assertEquals(7, $curta_count, "Deve ter exatamente 7 inscrições de Curta Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionadas pelo menos algumas Pessoas Negras
        $this->assertGreaterThanOrEqual(2, $negra_count, "Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas (principalmente de Curta)");

        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "Deve ter pelo menos alguns cotistas classificados");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e cota
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "[LIMIT 10] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "[LIMIT 10] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica as cotas (podem estar parcialmente preenchidas)
        $this->assertGreaterThanOrEqual(2, $negra_count, "[LIMIT 10] Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas");
        
        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "[LIMIT 10] Deve ter pelo menos alguns cotistas classificados");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 10] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }
            }
        }

        $this->assertEquals(3, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(2, $negra_count, "[PAGINAÇÃO] Deve ter pelo menos 2 inscrições de Pessoas Negras classificadas");
        
        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "[PAGINAÇÃO] Deve ter pelo menos alguns cotistas classificados");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesAndTerritoryVacanciesClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealRangesAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém o nome do campo regiao
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_regiao},score,eligible",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 10;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas
        $this->assertEquals(3, $longa_count, "Deve ter exatamente 3 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 7 Curtas
        $this->assertEquals(7, $curta_count, "Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica que foram selecionadas aproximadamente 5 da Capital (50% de 10)
        $this->assertGreaterThanOrEqual(4, $capital_count, "Deve ter pelo menos 4 inscrições da Região da Capital classificadas");
        $this->assertLessThanOrEqual(6, $capital_count, "Deve ter no máximo 6 inscrições da Região da Capital classificadas");

        // Verifica que foram selecionadas aproximadamente 3 do Litoral (30% de 10)
        $this->assertGreaterThanOrEqual(2, $coastal_count, "Deve ter pelo menos 2 inscrições da Região Litorânea classificadas");
        $this->assertLessThanOrEqual(4, $coastal_count, "Deve ter no máximo 4 inscrições da Região Litorânea classificadas");

        // Verifica que foram selecionadas aproximadamente 2 do Interior (20% de 10)
        $this->assertGreaterThanOrEqual(1, $interior_count, "Deve ter pelo menos 1 inscrição da Região do Interior classificada");
        $this->assertLessThanOrEqual(3, $interior_count, "Deve ter no máximo 3 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_regiao},score,eligible",
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Conta as inscrições classificadas
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica faixas
        $this->assertEquals(3, $longa_count, "[LIMIT 10] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[LIMIT 10] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica regiões (com margem de tolerância)
        $this->assertGreaterThanOrEqual(4, $capital_count, "[LIMIT 10] Deve ter pelo menos 4 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(2, $coastal_count, "[LIMIT 10] Deve ter pelo menos 2 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(1, $interior_count, "[LIMIT 10] Deve ter pelo menos 1 inscrição da Região do Interior classificada");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 10] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_regiao},score,eligible",
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $this->assertEquals(3, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(4, $capital_count, "[PAGINAÇÃO] Deve ter pelo menos 4 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(2, $coastal_count, "[PAGINAÇÃO] Deve ter pelo menos 2 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(1, $interior_count, "[PAGINAÇÃO] Deve ter pelo menos 1 inscrição da Região do Interior classificada");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesAndTerritoryVacanciesClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedRangesAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém o nome do campo regiao
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_regiao},score,eligible",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 10;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 3 Longas (faixas não podem variar)
        $this->assertEquals(3, $longa_count, "Deve ter exatamente 3 inscrições de Longa Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionados exatamente 7 Curtas (faixas não podem variar)
        $this->assertEquals(7, $curta_count, "Deve ter exatamente 7 inscrições de Curta Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionados 10 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(10, $total_selected, "Deve ter exatamente 10 inscrições classificadas no total");

        // Verifica que foram selecionados apenas alguns do Interior (não 2, pois faltam candidatos qualificados em Longa)
        $this->assertLessThanOrEqual(2, $interior_count, "Deve ter no máximo 2 inscrições da Região do Interior classificadas (faltam candidatos qualificados em Longa)");

        // Verifica que Capital e Litoral receberam mais vagas (redistribuição do Interior)
        $this->assertGreaterThanOrEqual(5, $capital_count, "A Capital deve ter pelo menos 5 inscrições (recebeu vagas redistribuídas do Interior)");
        $this->assertGreaterThanOrEqual(3, $coastal_count, "O Litoral deve ter pelo menos 3 inscrições (recebeu vagas redistribuídas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_regiao},score,eligible",
            '@order' => '@quota',
            '@limit' => 10,
        ], true);

        // Conta as inscrições classificadas
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica faixas
        $this->assertEquals(3, $longa_count, "[LIMIT 10] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[LIMIT 10] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");

        // Verifica total e redistribuição
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(10, $total_selected, "[LIMIT 10] Deve ter exatamente 10 inscrições classificadas no total");
        $this->assertLessThanOrEqual(2, $interior_count, "[LIMIT 10] Deve ter no máximo 2 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual(5, $capital_count, "[LIMIT 10] A Capital deve ter pelo menos 5 inscrições");
        $this->assertGreaterThanOrEqual(3, $coastal_count, "[LIMIT 10] O Litoral deve ter pelo menos 3 inscrições");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 10] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 5 em 5
        $longa_count = 0;
        $curta_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 2; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_regiao},score,eligible",
                '@order' => '@quota',
                '@limit' => 5,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $this->assertEquals(3, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 3 inscrições de Longa Metragem classificadas");
        $this->assertEquals(7, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 7 inscrições de Curta Metragem classificadas");
        
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(10, $total_selected, "[PAGINAÇÃO] Deve ter exatamente 10 inscrições classificadas no total");
        $this->assertLessThanOrEqual(2, $interior_count, "[PAGINAÇÃO] Deve ter no máximo 2 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual(5, $capital_count, "[PAGINAÇÃO] A Capital deve ter pelo menos 5 inscrições");
        $this->assertGreaterThanOrEqual(3, $coastal_count, "[PAGINAÇÃO] O Litoral deve ter pelo menos 3 inscrições");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testQuotasAndTerritoryVacanciesClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithQuotasAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealQuotasAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 100;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionadas pelo menos 20 Pessoas Negras (20% de 100)
        $this->assertGreaterThanOrEqual(20, $negra_count, "Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas (20% de 100)");

        // Verifica que foram selecionados pelo menos 5 Indígenas (5% de 100)
        $this->assertGreaterThanOrEqual(5, $indigena_count, "Deve ter pelo menos 5 inscrições de Indígenas classificadas (5% de 100)");

        // Verifica que foram selecionados pelo menos 2 PCD (2% de 100)
        $this->assertGreaterThanOrEqual(2, $pcd_count, "Deve ter pelo menos 2 inscrições de PCD classificadas (2% de 100)");

        // Verifica que foram selecionadas aproximadamente 50 da Capital (50% de 100)
        $this->assertGreaterThanOrEqual(45, $capital_count, "Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertLessThanOrEqual(55, $capital_count, "Deve ter no máximo 55 inscrições da Região da Capital classificadas");

        // Verifica que foram selecionadas aproximadamente 30 do Litoral (30% de 100)
        $this->assertGreaterThanOrEqual(25, $coastal_count, "Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertLessThanOrEqual(35, $coastal_count, "Deve ter no máximo 35 inscrições da Região Litorânea classificadas");

        // Verifica que foram selecionadas aproximadamente 20 do Interior (20% de 100)
        $this->assertGreaterThanOrEqual(15, $interior_count, "Deve ter pelo menos 15 inscrições da Região do Interior classificadas");
        $this->assertLessThanOrEqual(25, $interior_count, "Deve ter no máximo 25 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota e região
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica as cotas
        $this->assertGreaterThanOrEqual(20, $negra_count, "[LIMIT 100] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(5, $indigena_count, "[LIMIT 100] Deve ter pelo menos 5 inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(2, $pcd_count, "[LIMIT 100] Deve ter pelo menos 2 inscrições de PCD classificadas");

        // Verifica as regiões
        $this->assertGreaterThanOrEqual(45, $capital_count, "[LIMIT 100] Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertLessThanOrEqual(55, $capital_count, "[LIMIT 100] Deve ter no máximo 55 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[LIMIT 100] Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertLessThanOrEqual(35, $coastal_count, "[LIMIT 100] Deve ter no máximo 35 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(15, $interior_count, "[LIMIT 100] Deve ter pelo menos 15 inscrições da Região do Interior classificadas");
        $this->assertLessThanOrEqual(25, $interior_count, "[LIMIT 100] Deve ter no máximo 25 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 100] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $this->assertGreaterThanOrEqual(20, $negra_count, "[PAGINAÇÃO] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(5, $indigena_count, "[PAGINAÇÃO] Deve ter pelo menos 5 inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(2, $pcd_count, "[PAGINAÇÃO] Deve ter pelo menos 2 inscrições de PCD classificadas");
        $this->assertGreaterThanOrEqual(45, $capital_count, "[PAGINAÇÃO] Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertLessThanOrEqual(55, $capital_count, "[PAGINAÇÃO] Deve ter no máximo 55 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[PAGINAÇÃO] Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertLessThanOrEqual(35, $coastal_count, "[PAGINAÇÃO] Deve ter no máximo 35 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(15, $interior_count, "[PAGINAÇÃO] Deve ter pelo menos 15 inscrições da Região do Interior classificadas");
        $this->assertLessThanOrEqual(25, $interior_count, "[PAGINAÇÃO] Deve ter no máximo 25 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testQuotasAndTerritoryVacanciesClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithQuotasAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedQuotasAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 100;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados 100 no total (mesmo com falta de candidatos do Interior)
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "Deve ter exatamente 100 inscrições classificadas no total");

        // Verifica que foram selecionadas pelo menos algumas Pessoas Negras (cota tem prioridade)
        $this->assertGreaterThanOrEqual(15, $negra_count, "Deve ter pelo menos 15 inscrições de Pessoas Negras classificadas (cota tem prioridade)");

        // Verifica que cotas foram parcialmente preenchidas (pode haver redistribuição entre cotas)
        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "Deve ter pelo menos alguns cotistas classificados");

        // Verifica que foram selecionadas menos inscrições do Interior (não 20, pois faltam candidatos qualificados)
        $this->assertLessThan(20, $interior_count, "Deve ter menos de 20 inscrições da Região do Interior classificadas (faltam candidatos qualificados)");

        // Verifica que as vagas remanescentes do Interior foram distribuídas para Capital/Litoral
        // As vagas restantes do Interior devem ter sido preenchidas por outras regiões
        $this->assertGreaterThan(50, $capital_count, "A Capital deve ter mais de 50 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(30, $coastal_count, "O Litoral deve ter mais de 30 inscrições (recebeu vagas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada cota e região
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados 100 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[LIMIT 100] Deve ter exatamente 100 inscrições classificadas no total");

        // Verifica as cotas (podem estar parcialmente preenchidas)
        $this->assertGreaterThanOrEqual(15, $negra_count, "[LIMIT 100] Deve ter pelo menos 15 inscrições de Pessoas Negras classificadas");
        
        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "[LIMIT 100] Deve ter pelo menos alguns cotistas classificados");

        // Verifica que foram selecionadas menos inscrições do Interior
        $this->assertLessThan(20, $interior_count, "[LIMIT 100] Deve ter menos de 20 inscrições da Região do Interior classificadas");

        // Verifica que as vagas remanescentes do Interior foram distribuídas
        $this->assertGreaterThan(50, $capital_count, "[LIMIT 100] A Capital deve ter mais de 50 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(30, $coastal_count, "[LIMIT 100] O Litoral deve ter mais de 30 inscrições (recebeu vagas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 100] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[PAGINAÇÃO] Deve ter exatamente 100 inscrições classificadas no total");
        $this->assertLessThan(20, $interior_count, "[PAGINAÇÃO] Deve ter menos de 20 inscrições da Região do Interior classificadas");
        $this->assertGreaterThan(50, $capital_count, "[PAGINAÇÃO] A Capital deve ter mais de 50 inscrições (recebeu vagas do Interior)");
        $this->assertGreaterThan(30, $coastal_count, "[PAGINAÇÃO] O Litoral deve ter mais de 30 inscrições (recebeu vagas do Interior)");
        
        $total_quotists = $negra_count + $indigena_count + $pcd_count;
        $this->assertGreaterThan(0, $total_quotists, "[PAGINAÇÃO] Deve ter pelo menos alguns cotistas classificados");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesQuotasAndTerritoryVacanciesClassificationIdeal()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesQuotasAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário ideal
        $registrations = $this->quotaRegistrationDirector->idealRangesQuotasAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa, cota e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 100;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 30 Longas (faixas não podem variar)
        $this->assertEquals(30, $longa_count, "Deve ter exatamente 30 inscrições de Longa Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionados exatamente 70 Curtas (faixas não podem variar)
        $this->assertEquals(70, $curta_count, "Deve ter exatamente 70 inscrições de Curta Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionadas pelo menos 20 Pessoas Negras (20% de 100)
        $this->assertGreaterThanOrEqual(20, $negra_count, "Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas (20% de 100)");

        // Verifica que foram selecionados pelo menos 5 Indígenas (5% de 100)
        $this->assertGreaterThanOrEqual(5, $indigena_count, "Deve ter pelo menos 5 inscrições de Indígenas classificadas (5% de 100)");

        // Verifica que foram selecionados pelo menos 2 PCD (2% de 100)
        $this->assertGreaterThanOrEqual(2, $pcd_count, "Deve ter pelo menos 2 inscrições de PCD classificadas (2% de 100)");

        // Verifica que foram selecionados 100 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "Deve ter exatamente 100 inscrições classificadas no total");

        // Verifica distribuição regional (com margem de tolerância)
        $this->assertGreaterThanOrEqual(45, $capital_count, "Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertLessThanOrEqual(55, $capital_count, "Deve ter no máximo 55 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertLessThanOrEqual(35, $coastal_count, "Deve ter no máximo 35 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(15, $interior_count, "Deve ter pelo menos 15 inscrições da Região do Interior classificadas");
        $this->assertLessThanOrEqual(25, $interior_count, "Deve ter no máximo 25 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Conta as inscrições classificadas
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica faixas
        $this->assertEquals(30, $longa_count, "[LIMIT 100] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");
        $this->assertEquals(70, $curta_count, "[LIMIT 100] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica cotas
        $this->assertGreaterThanOrEqual(20, $negra_count, "[LIMIT 100] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(5, $indigena_count, "[LIMIT 100] Deve ter pelo menos 5 inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(2, $pcd_count, "[LIMIT 100] Deve ter pelo menos 2 inscrições de PCD classificadas");

        // Verifica total e redistribuição
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[LIMIT 100] Deve ter exatamente 100 inscrições classificadas no total");
        $this->assertGreaterThanOrEqual(45, $capital_count, "[LIMIT 100] Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[LIMIT 100] Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(15, $interior_count, "[LIMIT 100] Deve ter pelo menos 15 inscrições da Região do Interior classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 100] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $this->assertEquals(30, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");
        $this->assertEquals(70, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(20, $negra_count, "[PAGINAÇÃO] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(5, $indigena_count, "[PAGINAÇÃO] Deve ter pelo menos 5 inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(2, $pcd_count, "[PAGINAÇÃO] Deve ter pelo menos 2 inscrições de PCD classificadas");
        
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[PAGINAÇÃO] Deve ter exatamente 100 inscrições classificadas no total");
        $this->assertGreaterThanOrEqual(45, $capital_count, "[PAGINAÇÃO] Deve ter pelo menos 45 inscrições da Região da Capital classificadas");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[PAGINAÇÃO] Deve ter pelo menos 25 inscrições da Região Litorânea classificadas");
        $this->assertGreaterThanOrEqual(15, $interior_count, "[PAGINAÇÃO] Deve ter pelo menos 15 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }

    function testRangesQuotasAndTerritoryVacanciesClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRangesQuotasAndTerritoryVacancies($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->restrictedRangesQuotasAndTerritoryVacanciesScenario($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém os nomes corretos dos campos
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        // Obtém a classificação ordenada por cotas
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
        ], true);

        // Conta as inscrições classificadas respeitando os limites de cada faixa, cota e região
        $cutoff_score = $opportunity->evaluationMethodConfiguration->cutoffScore;
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;
        $total_vacancies = 100;

        for($i = 0; $i < $total_vacancies && $i < count($query_result->registrations); $i++) {
            $registration = $query_result->registrations[$i];
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica que foram selecionados exatamente 30 Longas (faixas não podem variar)
        $this->assertEquals(30, $longa_count, "Deve ter exatamente 30 inscrições de Longa Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionados exatamente 70 Curtas (faixas não podem variar)
        $this->assertEquals(70, $curta_count, "Deve ter exatamente 70 inscrições de Curta Metragem classificadas (faixas não podem variar)");

        // Verifica que foram selecionadas pelo menos 20 Pessoas Negras (20% de 100)
        // As cotas têm prioridade e podem ser redistribuídas entre si
        $this->assertGreaterThanOrEqual(20, $negra_count, "Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas (cotas têm prioridade)");

        // Verifica que foram selecionados pelo menos alguns Indígenas (pode ser menos que 5 se faltarem candidatos)
        // Mas deve tentar garantir o máximo possível
        $this->assertGreaterThanOrEqual(3, $indigena_count, "Deve ter pelo menos alguns inscrições de Indígenas classificadas (faltam candidatos qualificados em Longa no Interior)");

        // Verifica que foram selecionados pelo menos alguns PCD (pode ser menos que 2 se faltarem candidatos)
        // Mas deve tentar garantir o máximo possível
        $this->assertGreaterThanOrEqual(1, $pcd_count, "Deve ter pelo menos algumas inscrições de PCD classificadas (faltam candidatos qualificados em Longa no Interior)");

        // Verifica que foram selecionados 100 no total
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "Deve ter exatamente 100 inscrições classificadas no total");

        // Verifica que foram selecionados menos do Interior em Longa (não 6, pois faltam candidatos qualificados)
        // As vagas do Interior podem ser redistribuídas para outras regiões
        $this->assertLessThanOrEqual(20, $interior_count, "Deve ter no máximo 20 inscrições da Região do Interior classificadas (faltam candidatos qualificados em Longa)");

        // Verifica que Capital e Litoral receberam mais vagas (redistribuição do Interior)
        $this->assertGreaterThanOrEqual(45, $capital_count, "A Capital deve ter pelo menos 45 inscrições (recebeu vagas redistribuídas do Interior)");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "O Litoral deve ter pelo menos 25 inscrições (recebeu vagas redistribuídas do Interior)");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com limite

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Conta as inscrições classificadas
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        foreach($query_result->registrations as $registration) {
            $lowest_score = min($lowest_score, $registration['score']);

            // Conta por faixa
            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }

            // Conta por cota
            $quotas = $registration['quotas'] ?? [];
            if (!empty($quotas)) {
                if (in_array('Pessoas Negras', $quotas)) {
                    $negra_count++;
                }
                
                if (in_array('Indígenas', $quotas)) {
                    $indigena_count++;
                }
                
                if (in_array('PCD', $quotas)) {
                    $pcd_count++;
                }
            }

            // Conta por região
            $region = $registration[$field_regiao] ?? null;
            if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                $capital_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                $coastal_count++;
            } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                $interior_count++;
            }
        }

        // Verifica faixas
        $this->assertEquals(30, $longa_count, "[LIMIT 100] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");
        $this->assertEquals(70, $curta_count, "[LIMIT 100] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica cotas (com tolerância para redistribuição)
        $this->assertGreaterThanOrEqual(20, $negra_count, "[LIMIT 100] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(3, $indigena_count, "[LIMIT 100] Deve ter pelo menos alguns inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(1, $pcd_count, "[LIMIT 100] Deve ter pelo menos algumas inscrições de PCD classificadas");

        // Verifica total e redistribuição
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[LIMIT 100] Deve ter exatamente 100 inscrições classificadas no total");
        $this->assertLessThanOrEqual(20, $interior_count, "[LIMIT 100] Deve ter no máximo 20 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual(45, $capital_count, "[LIMIT 100] A Capital deve ter pelo menos 45 inscrições");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[LIMIT 100] O Litoral deve ter pelo menos 25 inscrições");

        // Verifica que todas as inscrições selecionadas têm nota >= nota de corte
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[LIMIT 100] A menor nota deve ser >= {$cutoff_score} (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $longa_count = 0;
        $curta_count = 0;
        $negra_count = 0;
        $indigena_count = 0;
        $pcd_count = 0;
        $capital_count = 0;
        $coastal_count = 0;
        $interior_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => "number,range,{$field_raca},{$field_pessoa_deficiente},{$field_regiao},score,eligible,quotas",
                '@order' => '@quota',
                '@limit' => 10,
                '@page' => $page,
            ], true);

            foreach($query_result->registrations as $registration) {
                $lowest_score = min($lowest_score, $registration['score']);
                
                // Conta por faixa
                if ($registration['range'] === 'Longa Metragem') {
                    $longa_count++;
                } elseif ($registration['range'] === 'Curta Metragem') {
                    $curta_count++;
                }

                // Conta por cota
                $quotas = $registration['quotas'] ?? [];
                if (!empty($quotas)) {
                    if (in_array('Pessoas Negras', $quotas)) {
                        $negra_count++;
                    }
                    
                    if (in_array('Indígenas', $quotas)) {
                        $indigena_count++;
                    }
                    
                    if (in_array('PCD', $quotas)) {
                        $pcd_count++;
                    }
                }

                // Conta por região
                $region = $registration[$field_regiao] ?? null;
                if ($region === QuotaRegistrationDirector::REGION_CAPITAL) {
                    $capital_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_COASTAL) {
                    $coastal_count++;
                } elseif ($region === QuotaRegistrationDirector::REGION_INTERIOR) {
                    $interior_count++;
                }
            }
        }

        $this->assertEquals(30, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");
        $this->assertEquals(70, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");
        $this->assertGreaterThanOrEqual(20, $negra_count, "[PAGINAÇÃO] Deve ter pelo menos 20 inscrições de Pessoas Negras classificadas");
        $this->assertGreaterThanOrEqual(3, $indigena_count, "[PAGINAÇÃO] Deve ter pelo menos alguns inscrições de Indígenas classificadas");
        $this->assertGreaterThanOrEqual(1, $pcd_count, "[PAGINAÇÃO] Deve ter pelo menos algumas inscrições de PCD classificadas");
        
        $total_selected = $capital_count + $coastal_count + $interior_count;
        $this->assertEquals(100, $total_selected, "[PAGINAÇÃO] Deve ter exatamente 100 inscrições classificadas no total");
        $this->assertLessThanOrEqual(20, $interior_count, "[PAGINAÇÃO] Deve ter no máximo 20 inscrições da Região do Interior classificadas");
        $this->assertGreaterThanOrEqual(45, $capital_count, "[PAGINAÇÃO] A Capital deve ter pelo menos 45 inscrições");
        $this->assertGreaterThanOrEqual(25, $coastal_count, "[PAGINAÇÃO] O Litoral deve ter pelo menos 25 inscrições");
        $this->assertGreaterThanOrEqual($cutoff_score, $lowest_score, "[PAGINAÇÃO] A menor nota deve ser >= {$cutoff_score} (nota de corte)");
    }
}
