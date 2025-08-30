<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationsDistributionTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testConcurrentEvaluationPhaseDistribution()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->getInstance();

        $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $valuer1_summary = $emc->agentRelations[0]->metadata['summary'];
        $valuer2_summary = $emc->agentRelations[1]->metadata['summary'];

        $this->assertEquals(5, $valuer1_summary['pending'], 'Garantindo que o avaliador 1 tem 5 avaliações pendentes');
        $this->assertEquals(5, $valuer2_summary['pending'], 'Garantindo que o avaliador 2 tem 5 avaliações pendentes');

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");

        $this->assertEquals(10, $number_of_evaluations, 'Garantindo que tenha 10 avaliações');
    }

    function testFirstEvaluationPhaseDeletion()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->save()
                                    ->done()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->refresh()
                                ->getInstance();


        $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();
        $phases = $opportunity->phases;
        $evaluation_method_configuration_id = $phases[1]->id;

        $app = $this->app;

        /** @var EvaluationMethodConfiguration */
        $emc = $app->repo('EvaluationMethodConfiguration')->find($evaluation_method_configuration_id);
        
        $emc->redistributeCommitteeRegistrations();
        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $emc->refreshed();

        $valuer1_summary = $emc->agentRelations[0]->metadata['summary'];
        $valuer2_summary = $emc->agentRelations[1]->metadata['summary'];
        
        $this->assertEquals(5, $valuer1_summary['pending'], 'Garantindo que o avaliador 1 tem 5 avaliações pendentes');
        $this->assertEquals(5, $valuer2_summary['pending'], 'Garantindo que o avaliador 2 tem 5 avaliações pendentes');

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");

        $this->assertEquals(10, $number_of_evaluations, 'Garantindo que tenha 10 avaliações');
    }

    function testCategoryFilteredUniqueEvaluatorDistribution()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuers_per_committe = 10;
        $registrations_per_category = 20;
        $valuers_per_registrations = 2;
        $expected_evaluations_per_valuer = $registrations_per_category * $valuers_per_registrations / $valuers_per_committe;
        $expected_total_per_committe = $registrations_per_category * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->addCategory('Cat1')
                                ->addCategory('Cat2')
                                ->addCategory('Cat3')
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 1', ['Cat1'])
                                    ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 2', ['Cat2'])
                                    ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 3', ['Cat3'])
                                    ->save()
                                    ->addValuers($valuers_per_committe, 'committee 1')
                                    ->addValuers($valuers_per_committe, 'committee 2')
                                    ->addValuers($valuers_per_committe, 'committee 3')
                                    ->done()
                                ->getInstance();


        $categories = [
            'Cat1' => ['sent' => $registrations_per_category, 'draft' => 3],
            'Cat2' => ['sent' => $registrations_per_category, 'draft' => 3],
            'Cat3' => ['sent' => $registrations_per_category, 'draft' => 3],
        ];

        // Cria inscrições "enviadas" e "rascunho" para cada categoria especificada
        foreach ($categories as $category => $counts) {
            $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations: $counts['sent'],
                category: $category
            );

            $this->registrationDirector->createDraftRegistrations(
                $opportunity,
                number_of_registrations: $counts['draft'],
                category: $category
            );
        }

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $committees = [];
        foreach($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        foreach($committees as $committee => $relations) {
            foreach($relations as $relation) {
                $valuer_agent_id = $relation->agent->id;
                $pending_summary = $relation->metadata['summary']['pending'];

                // Verifica se o total de avaliações por comissão do avaliador está correto
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                        AND valuer_committee = :committee
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                    'committee' => $committee
                ];
                $evaluations_per_committee_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($expected_evaluations_per_valuer, $evaluations_per_committee_count, "[Sem repetição de avaliador] Garantindo que o avaliador de id {$valuer_agent_id} na comissão {$committee} tem exatamente {$expected_evaluations_per_valuer} avaliações; obtido {$evaluations_per_committee_count}.");

                // Verifica se o sumário do avaliador corresponde ao total na tabela evaluations
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                ];
                $evaluations_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($pending_summary, $evaluations_count, "[Sem repetição de avaliador] Garantindo que o sumário de avaliações pendentes ({$pending_summary}) do avaliador {$valuer_agent_id} corresponde ao total de avaliações na tabela evaluations ({$evaluations_count}).");
            }

            // Verifica se o total de avaliações por comissão corresponde a soma do total de pendentes da comissão
            $pendings = array_sum(array_map(fn($rel) => $rel->metadata['summary']['pending'], $relations));
            $this->assertEquals($expected_total_per_committe, $pendings, "[Sem repetição de avaliador] Garantindo que cada avaliador da comissão {$committee} deve ter exatamente {$expected_total_per_committe} pendentes; obtido {$pendings}.");
        }

        // Verifica se o total de avaliações da oportunidade corresponde ao total de avaliações esperada
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE opportunity_id = :opportunity_id", ['opportunity_id' => $opportunity->id]);
        $total_evaluations = array_sum(array_map(fn($category_counts) => $category_counts['sent'] * $valuers_per_registrations, $categories));

        $this->assertEquals($total_evaluations, $number_of_evaluations, "[Sem repetição de avaliador] Garantindo que tenha {$total_evaluations} avaliações");
    }
    
    function testCategoryFilteredRepeatedEvaluatorDistribution()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 10;
        $registrations_per_category = 20;
        $valuers_per_registrations = 2;
        $expected_evaluations_per_valuer = $registrations_per_category * $valuers_per_registrations / $valuers_per_committe;
        $expected_total_per_committe = $registrations_per_category * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->addCategory('Cat1')
                                ->addCategory('Cat2')
                                ->addCategory('Cat3')
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 1', ['Cat1'])
                                    ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 2', ['Cat2'])
                                    ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                                    ->setCommitteeFilterCategory('committee 3', ['Cat3'])
                                    ->save()
                                    ->addValuers($valuers_per_committe - 1, 'committee 1')
                                    ->addValuers($valuers_per_committe - 1, 'committee 2')
                                    ->addValuers($valuers_per_committe, 'committee 3')
                                    ->addValuer('committee 1', $valuer->profile)
                                    ->addValuer('committee 2', $valuer->profile)
                                    ->done()
                                ->getInstance();


        // 60 inscrições enviadas sendo 20 por categorias
        $categories = [
            'Cat1' => ['sent' => $registrations_per_category, 'draft' => 3],
            'Cat2' => ['sent' => $registrations_per_category, 'draft' => 3],
            'Cat3' => ['sent' => $registrations_per_category, 'draft' => 3],
        ];

        // Cria inscrições "enviadas" e "rascunho" para cada categoria especificada
        foreach ($categories as $category => $counts) {
            $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations: $counts['sent'],
                category: $category
            );

            $this->registrationDirector->createDraftRegistrations(
                $opportunity,
                number_of_registrations: $counts['draft'],
                category: $category
            );
        }

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $committees = [];
        foreach($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        foreach($committees as $committee => $relations) {
            foreach($relations as $relation) {
                $valuer_agent_id = $relation->agent->id;
                $pending_summary = $relation->metadata['summary']['pending'];

                // Verifica se o total de avaliações por comissão do avaliador está correto
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                        AND valuer_committee = :committee
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                    'committee' => $committee
                ];
                $evaluations_per_committee_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($expected_evaluations_per_valuer, $evaluations_per_committee_count, "[Avaliador repetido] Garantindo que o avaliador de id {$valuer_agent_id} na comissão {$committee} tem exatamente {$expected_evaluations_per_valuer} avaliações; obtido {$evaluations_per_committee_count}.");

                // Verifica se o sumário do avaliador corresponde ao total na tabela evaluations
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                ];
                $evaluations_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($pending_summary, $evaluations_count, "[Avaliador repetido] Garantindo que o sumário de avaliações pendentes ({$pending_summary}) do avaliador {$valuer_agent_id} corresponde ao total de avaliações na tabela evaluations ({$evaluations_count}).");
            }

            // Verifica se o total de avaliações por comissão corresponde a soma do total de pendentes da comissão
            $pendings = array_sum(array_map(fn($rel) => $rel->metadata['summary']['pending'], $relations));
            $this->assertEquals($expected_total_per_committe, $pendings, "[Avaliador repetido] Garantindo que cada avaliador da comissão {$committee} deve ter exatamente {$expected_total_per_committe} pendentes; obtido {$pendings}.");
        }

        // Verifica se o total de avaliações da oportunidade corresponde ao total de avaliações esperada
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE opportunity_id = :opportunity_id", ['opportunity_id' => $opportunity->id]);
        $total_evaluations = array_sum(array_map(fn($category_counts) => $category_counts['sent'] * $valuers_per_registrations, $categories));

        $this->assertEquals($total_evaluations, $number_of_evaluations, "[Avaliador repetido] Garantindo que tenha {$total_evaluations} avaliações");
    }

    function testProponentTypeFilteredRepeatedEvaluatorDistribution()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 10;
        $registrations_per_proponent_type = 20;
        $valuers_per_registrations = 2;
        $expected_evaluations_per_valuer = $registrations_per_proponent_type * $valuers_per_registrations / $valuers_per_committe;
        $expected_total_per_committe = $registrations_per_proponent_type * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->addProponentType('MEI')
                                ->addProponentType('Pessoa Jurídica')
                                ->addProponentType('Coletivo')
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                                    ->setCommitteeFilterProponentType('committee 1', ['MEI'])
                                    ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                                    ->setCommitteeFilterProponentType('committee 2', ['Coletivo'])
                                    ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                                    ->setCommitteeFilterProponentType('committee 3', ['Pessoa Física'])
                                    ->save()
                                    ->addValuers($valuers_per_committe - 1, 'committee 1')
                                    ->addValuers($valuers_per_committe - 1, 'committee 2')
                                    ->addValuers($valuers_per_committe, 'committee 3')
                                    ->addValuer('committee 1', $valuer->profile)
                                    ->addValuer('committee 2', $valuer->profile)
                                    ->done()
                                ->getInstance();


        // 60 inscrições enviadas sendo 20 por tipos de proponente
        $proponent_types = [
            'MEI' => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
            'Pessoa Jurídica' => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
            'Coletivo' => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
        ];

        // Cria inscrições "enviadas" e "rascunho" para cada tipo de proponente especificado
        foreach ($proponent_types as $proponent_type => $counts) {
            $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations: $counts['sent'],
                proponent_type: $proponent_type
            );

            $this->registrationDirector->createDraftRegistrations(
                $opportunity,
                number_of_registrations: $counts['draft'],
                proponent_type: $proponent_type
            );
        }

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $committees = [];
        foreach($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        foreach($committees as $committee => $relations) {
            foreach($relations as $relation) {
                $valuer_agent_id = $relation->agent->id;
                $pending_summary = $relation->metadata['summary']['pending'];

                // Verifica se o total de avaliações por comissão do avaliador está correto
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                        AND valuer_committee = :committee
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                    'committee' => $committee
                ];
                $evaluations_per_committee_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($expected_evaluations_per_valuer, $evaluations_per_committee_count, "[Avaliador repetido] Garantindo que o avaliador de id {$valuer_agent_id} na comissão {$committee} tem exatamente {$expected_evaluations_per_valuer} avaliações; obtido {$evaluations_per_committee_count}.");

                // Verifica se o sumário do avaliador corresponde ao total na tabela evaluations
                $query = "
                    SELECT COUNT(*) 
                    FROM evaluations 
                    WHERE valuer_agent_id = :valuer_agent_id 
                ";
                $params = [
                    'valuer_agent_id' => $valuer_agent_id,
                ];
                $evaluations_count = $conn->fetchScalar($query, $params);

                $this->assertEquals($pending_summary, $evaluations_count, "[Avaliador repetido] Garantindo que o sumário de avaliações pendentes ({$pending_summary}) do avaliador {$valuer_agent_id} corresponde ao total de avaliações na tabela evaluations ({$evaluations_count}).");
            }

            // Verifica se o total de avaliações por comissão corresponde a soma do total de pendentes da comissão
            $pendings = array_sum(array_map(fn($rel) => $rel->metadata['summary']['pending'], $relations));
            $this->assertEquals($expected_total_per_committe, $pendings, "[Avaliador repetido] Garantindo que cada avaliador da comissão {$committee} deve ter exatamente {$expected_total_per_committe} pendentes; obtido {$pendings}.");
        }

        // Verifica se o total de avaliações da oportunidade corresponde ao total de avaliações esperada
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE opportunity_id = :opportunity_id", ['opportunity_id' => $opportunity->id]);
        $total_evaluations = array_sum(array_map(fn($proponent_type_counts) => $proponent_type_counts['sent'] * $valuers_per_registrations, $proponent_types));

        $this->assertEquals($total_evaluations, $number_of_evaluations, "[Avaliador repetido] Garantindo que tenha {$total_evaluations} avaliações");
    }
}
