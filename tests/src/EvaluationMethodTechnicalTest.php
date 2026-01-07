<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
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

    protected function createOpportunityWithRanges($admin): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(100)
            ->addRange('Longa Metragem', 30, 0)
            ->addRange('Curta Metragem', 70, 0)
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

    protected function createOpportunityWithQuotas($admin): Opportunity
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
                    // ->setConsiderQuotasInGeneralList(true)
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

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (30 Longas + 70 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($i = 0; $i < 100; $i++) {
            $registration = $query_result->registrations[$i];

            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados exatamente 30 Longas
        $this->assertEquals(30, $longa_count, "Deve ter exatamente 30 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 70 Curtas
        $this->assertEquals(70, $curta_count, "Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (30 Longas + 70 Curtas)
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

        // Verifica que foram selecionados exatamente 30 Longas
        $this->assertEquals(30, $longa_count, "[LIMIT 100] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 70 Curtas
        $this->assertEquals(70, $curta_count, "[LIMIT 100] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[LIMIT 100] A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => 'number,range,score,eligible',
                '@order' => '@quota',
                '@limit' => 10,
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

        $this->assertEquals(30, $longa_count, "[PAGINAÇÃO] Deve ter exatamente 30 inscrições de Longa Metragem classificadas");
        $this->assertEquals(70, $curta_count, "[PAGINAÇÃO] Deve ter exatamente 70 inscrições de Curta Metragem classificadas");
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

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (máx. 30 Longas + 70 Curtas)
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($i = 0; $i < 100; $i++) {
            $registration = $query_result->registrations[$i];

            $lowest_score = min($lowest_score, $registration['score']);

            if ($registration['range'] === 'Longa Metragem') {
                $longa_count++;
            }

            if ($registration['range'] === 'Curta Metragem') {
                $curta_count++;
            }
        }

        // Verifica que foram selecionados 90 Curtas (70 da faixa + 20 que preenchem vagas de Longa não preenchidas)
        $this->assertEquals(90, $curta_count, "Deve ter 90 inscrições de Curta Metragem classificadas (70 da faixa + 20 preenchendo vagas de Longa)");

        // Verifica que foram selecionados apenas 10 Longas (não 30, pois faltam candidatos qualificados)
        $this->assertEquals(10, $longa_count, "Deve ter apenas 10 inscrições de Longa Metragem classificadas (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação

        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota',
            '@limit' => 100,
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (máx. 30 Longas + 70 Curtas)
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

        // Verifica que foram selecionados 90 Curtas (70 da faixa + 20 que preenchem vagas de Longa não preenchidas)
        $this->assertEquals(90, $curta_count, "[LIMIT 100] Deve ter 90 inscrições de Curta Metragem classificadas (70 da faixa + 20 preenchendo vagas de Longa)");

        // Verifica que foram selecionados apenas 10 Longas (não 30, pois faltam candidatos qualificados)
        $this->assertEquals(10, $longa_count, "[LIMIT 100] Deve ter apenas 10 inscrições de Longa Metragem classificadas (faltam candidatos qualificados)");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        $this->assertGreaterThanOrEqual(40.0, $lowest_score, "[LIMIT 100] A menor nota deve ser >= 40 (nota de corte)");

        // ================================
        // Testando com paginação 10 em 10
        $longa_count = 0;
        $curta_count = 0;
        $lowest_score = 100;

        for($page = 1; $page <= 10; $page++) {
            $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
                '@select' => 'number,range,score,eligible',
                '@order' => '@quota',
                '@limit' => 10,
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

        $this->assertEquals(90, $curta_count, "[PAGINAÇÃO] Deve ter 90 inscrições de Curta Metragem classificadas (70 da faixa + 20 preenchendo vagas de Longa)");
        $this->assertEquals(10, $longa_count, "[PAGINAÇÃO] Deve ter apenas 10 inscrições de Longa Metragem classificadas (faltam candidatos qualificados)");
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
        // Testando com paginação

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
}
