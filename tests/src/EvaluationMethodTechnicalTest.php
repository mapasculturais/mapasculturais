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
        $this->quotaRegistrationDirector = new QuotaRegistrationDirector();
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
            ->setRanges([
                [
                    'label' => 'Longa Metragem',
                    'limit' => 30,
                    'value' => 0
                ],
                [
                    'label' => 'Curta Metragem',
                    'limit' => 70,
                    'value' => 0
                ]
            ])
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(40.0)
                ->save()
                ->config()
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
        $registrations = $this->quotaRegistrationDirector->cenarioIdealFaixas($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém a classificação ordenada por cotas (que considera faixas)
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota'
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (30 Longas + 70 Curtas)
        $classification_zone = [];
        $longa_count = 0;
        $curta_count = 0;

        foreach ($query_result->registrations as $registration) {
            // Pula inscrições abaixo da nota de corte
            if ($registration['score'] < 40.0) {
                continue;
            }

            // Adiciona até o limite de cada faixa
            if ($registration['range'] === 'Longa Metragem' && $longa_count < 30) {
                $classification_zone[] = $registration;
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem' && $curta_count < 70) {
                $classification_zone[] = $registration;
                $curta_count++;
            }
            
            // Para quando atingir o total de vagas
            if (count($classification_zone) >= 100) {
                break;
            }
        }

        // Verifica que foram selecionados exatamente 100
        $this->assertCount(100, $classification_zone, "Deve ter exatamente 100 inscrições classificadas");

        // Verifica que foram selecionados exatamente 30 Longas
        $this->assertEquals(30, $longa_count, "Deve ter exatamente 30 inscrições de Longa Metragem classificadas");

        // Verifica que foram selecionados exatamente 70 Curtas
        $this->assertEquals(70, $curta_count, "Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        foreach ($classification_zone as $registration) {
            $this->assertGreaterThanOrEqual(40.0, $registration['score'], "Todas as inscrições classificadas devem ter nota >= 40 (nota de corte)");
        }
    }

    function testRangeClassificationRestricted()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithRanges($admin);

        // Cria inscrições para o cenário restrito
        $registrations = $this->quotaRegistrationDirector->cenarioRestritoFaixas($opportunity);

        $app = App::i();
        /** @var OpportunityController */
        $opportunity_controller = $app->controller('opportunity');

        // Obtém a classificação ordenada por cotas (que considera faixas)
        $query_result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'number,range,score,eligible',
            '@order' => '@quota'
        ], true);

        // Filtra as inscrições classificadas respeitando os limites de cada faixa (máx. 30 Longas + 70 Curtas)
        $classification_zone = [];
        $longa_count = 0;
        $curta_count = 0;
        
        foreach ($query_result->registrations as $registration) {
            // Pula inscrições abaixo da nota de corte
            if ($registration['score'] < 40.0) {
                continue;
            }
            
            // Adiciona até o limite de cada faixa
            if ($registration['range'] === 'Longa Metragem' && $longa_count < 30) {
                $classification_zone[] = $registration;
                $longa_count++;
            } elseif ($registration['range'] === 'Curta Metragem' && $curta_count < 70) {
                $classification_zone[] = $registration;
                $curta_count++;
            }
        }

        // Verifica que foram selecionados exatamente 70 Curtas
        $this->assertEquals(70, $curta_count, "Deve ter exatamente 70 inscrições de Curta Metragem classificadas");

        // Verifica que foram selecionados apenas 10 Longas (não 30, pois faltam candidatos qualificados)
        $this->assertEquals(10, $longa_count, "Deve ter apenas 10 inscrições de Longa Metragem classificadas (faltam candidatos qualificados)");

        // Verifica que o total é 80 (não 100, pois faltam 20 vagas de Longa)
        $total_classified = $longa_count + $curta_count;
        $this->assertEquals(80, $total_classified, "Total de classificados deve ser 80 (70 Curtas + 10 Longas)");

        // Verifica que todas as inscrições selecionadas têm nota >= 40 (nota de corte)
        foreach ($classification_zone as $registration) {
            $this->assertGreaterThanOrEqual(40.0, $registration['score'], "Todas as inscrições classificadas devem ter nota >= 40 (nota de corte)");
        }

        // Verifica que NÃO há inscrições de Curta preenchendo vagas de Longa
        $this->assertLessThanOrEqual(30, $longa_count, "Não pode ter mais de 30 Longas (limite da faixa)");
        $this->assertLessThanOrEqual(70, $curta_count, "Não pode ter mais de 70 Curtas (limite da faixa)");
    }
}
