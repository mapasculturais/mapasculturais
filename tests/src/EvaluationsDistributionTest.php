<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\DateTime;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Enums\ProponentTypes;
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
        /*
         tendo 10 inscrições por categoria e tendo 2 avaliadores por comissão,
         cada comissão terá um total de 20 avaliações

         tendo 5 avaliadores únicos, que não são avaliadores de outras comissões,
         cada avaliador recebe 4 inscrições para avaliar.


                      comissão 1   comissão 2    comissão 3      TOTAL DO AVALIADOR
        =========================+=============+==============+=====================
        avaliador 1       4                                              4              
        avaliador 2       4                                              4
        avaliador 3       4                                              4
        avaliador 4       4                                              4
        avaliador 5       4                                              4
        avaliador 6                   4                                  4
        avaliador 7                   4                                  4
        avaliador 8                   4                                  4
        avaliador 9                   4                                  4
        avaliador 10                  4                                  4
        avaliador 11                                 4                   4
        avaliador 12                                 4                   4
        avaliador 13                                 4                   4
        avaliador 14                                 4                   4
        avaliador 15                                 4                   4
        ----------------------------------------------------------------------------
        TOTAL da                                                                           
        comissão         20          20             20                  60

        */

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuers_per_committe = 5;
        $registrations_per_category = 10;
        $valuers_per_registrations = 2;
        $evaluations_per_valuer = $registrations_per_category * $valuers_per_registrations / $valuers_per_committe;

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


        // 30 inscrições enviadas sendo 10 por categorias
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
        foreach ($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // testar se cada comissão tem um total de 20 avaliações
        foreach (['committee 1', 'committee 2', 'committee 3'] as $committee_name) {
            $number_of_evaluations = $conn->fetchScalar("SELECT count(*) FROM evaluations WHERE valuer_committee = :committee", ['committee' => $committee_name]);

            $this->assertEquals($expected_total_per_committe, $number_of_evaluations, "[Avaliador único] Garantindo que o número de avaliações na comissão $committee_name está correto ($expected_total_per_committe)");
        }



        // testar se cada avaliador tem exatamente 4
        foreach ($committees as $committee => $relations) {
            foreach ($relations as $relation) {
                $valuer_id = $relation->agent->id;

                $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id", ['valuer_id' => $valuer_id]);

                $this->assertEquals($evaluations_per_valuer, $number_of_evaluations, "[Avaliador único] Garantindo que o avaliador da comissão {$committee} tem exatamente {$evaluations_per_valuer} avaliações");
            }
        }

        // testar se a soma de todos os sumários pendentes em cada comissão tem a soma de 20 avaliações
        $committee_total = [];
        foreach ($committees as $committee => $relations) {
            $committee_total = 0;
            foreach ($relations as $relation) {
                $committee_total += $relation->metadata['summary']['pending'];
            }

            $this->assertEquals($expected_total_per_committe, $committee_total, "[Avaliador único] Garantindo que a soma das inscrições pendentes dos resumos dos avaliadores da comissão $committee_name está correta");
        }
    }

    function testCategoryFilteredRepeatedEvaluatorDistribution()
    {
        /*
         tendo 10 inscrições por categoria e tendo 2 avaliadores por comissão,
         cada comissão terá um total de 20 avaliações

         tendo 5 avaliadores únicos, que não são avaliadores de outras comissões,
         cada avaliador recebe 4 inscrições para avaliar.

         se há um avaliador que está em mais de uma comissão, cada avaliador recebe, no total 
         entre 4 e 5 avaliações


                      comissão 1   comissão 2    comissão 3      TOTAL DO AVALIADOR
        =========================+=============+==============+=====================
        avaliador 1       3           2                                  5              
        avaliador 2       4                                              4
        avaliador 3       4                                              4
        avaliador 4       5                                              5
        avaliador 5       4                                              4
        avaliador 6                   4                                  4
        avaliador 7                   5                                  5
        avaliador 8                   4                                  4
        avaliador 9                   5                                  5
        avaliador 10                                 4                   4
        avaliador 11                                 4                   4
        avaliador 12                                 4                   4
        avaliador 13                                 4                   4
        avaliador 14                                 4                   4
        ----------------------------------------------------------------------------
        TOTAL da                                                                           
        comissão         20          20             20                  60

        */

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 5;
        $registrations_per_category = 10;
        $valuers_per_registrations = 2;
        $min_evaluations_per_valuer = $registrations_per_category * $valuers_per_registrations / $valuers_per_committe;
        $max_evaluations_per_valuer = $min_evaluations_per_valuer + 1;
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
                ->addValuer('committee 1', 'fulano', $valuer->profile)->done()
                ->addValuer('committee 2', 'ciclano', $valuer->profile)->done()
                ->done()
            ->getInstance();


        // 20 inscrições enviadas sendo 10 por categorias
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
        foreach ($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // testar se cada comissão tem um total de 20 avaliações
        foreach (['committee 1', 'committee 2', 'committee 3'] as $committee_name) {
            $number_of_evaluations = $conn->fetchScalar("SELECT count(*) FROM evaluations WHERE valuer_committee = :committee", ['committee' => $committee_name]);

            $this->assertEquals($expected_total_per_committe, $number_of_evaluations, "[Avaliador repetido] Garantindo que o número de avaliações na comissão $committee_name está correto ($expected_total_per_committe)");
        }

        // testar se cada avaliador tem entre 4 e 5 avaliações
        foreach ($committees as $committee => $relations) {
            foreach ($relations as $relation) {
                $valuer_id = $relation->agent->id;

                $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id", ['valuer_id' => $valuer_id]);

                $this->assertGreaterThanOrEqual($min_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} tem ao menos {$min_evaluations_per_valuer} avaliações");
                $this->assertLessThanOrEqual($max_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} não tem mais que {$max_evaluations_per_valuer} avaliações");
            }
        }

        // testar se a soma de todos os sumários pendentes em cada comissão tem a soma de 20 avaliações
        $committee_total = [];
        foreach ($committees as $committee => $relations) {
            $committee_total = 0;
            foreach ($relations as $relation) {
                $committee_total += $relation->metadata['summary']['pending'];
            }

            $this->assertEquals($expected_total_per_committe, $committee_total, "[Avaliador repetido] Garantindo que a soma das inscrições pendentes dos resumos dos avaliadores da comissão $committee_name está correta");
        }
    }

    function testProponentTypeFilteredRepeatedEvaluatorDistribution()
    {
        /*
         tendo 10 inscrições por categoria e tendo 2 avaliadores por comissão,
         cada comissão terá um total de 20 avaliações

         tendo 5 avaliadores únicos, que não são avaliadores de outras comissões,
         cada avaliador recebe 4 inscrições para avaliar.

         se há um avaliador que está em mais de uma comissão, cada avaliador recebe, no total 
         entre 4 e 5 avaliações


                      comissão 1   comissão 2    comissão 3      TOTAL DO AVALIADOR
        =========================+=============+==============+=====================
        avaliador 1       3           2                                  5              
        avaliador 2       4                                              4
        avaliador 3       4                                              4
        avaliador 4       5                                              5
        avaliador 5       4                                              4
        avaliador 6                   4                                  4
        avaliador 7                   5                                  5
        avaliador 8                   4                                  4
        avaliador 9                   5                                  5
        avaliador 10                                 4                   4
        avaliador 11                                 4                   4
        avaliador 12                                 4                   4
        avaliador 13                                 4                   4
        avaliador 14                                 4                   4
        ----------------------------------------------------------------------------
        TOTAL da                                                                           
        comissão         20          20             20                  60

        */

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 5;
        $registrations_per_proponent_type = 10;
        $valuers_per_registrations = 2;
        $min_evaluations_per_valuer = $registrations_per_proponent_type * $valuers_per_registrations / $valuers_per_committe;
        $max_evaluations_per_valuer = $min_evaluations_per_valuer + 1;
        $expected_total_per_committe = $registrations_per_proponent_type * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->addProponentType(ProponentTypes::PESSOA_FISICA)
            ->addProponentType(ProponentTypes::MEI)
            ->addProponentType(ProponentTypes::COLETIVO)
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                ->setCommitteeFilterProponentType('committee 1', [ProponentTypes::PESSOA_FISICA->value])
                ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                ->setCommitteeFilterProponentType('committee 2', [ProponentTypes::MEI->value])
                ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                ->setCommitteeFilterProponentType('committee 3', [ProponentTypes::COLETIVO->value])
                ->save()
                ->addValuers($valuers_per_committe - 1, 'committee 1')
                ->addValuers($valuers_per_committe - 1, 'committee 2')
                ->addValuers($valuers_per_committe, 'committee 3')
                ->addValuer('committee 1', 'fulano', $valuer->profile)->done()
                ->addValuer('committee 2', 'ciclano', $valuer->profile)->done()
                ->done()

            ->refresh()
            ->getInstance();

        
        // 30 inscrições enviadas sendo 10 por tipos de proponente
        $proponent_types = [
            ProponentTypes::PESSOA_FISICA->value => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
            ProponentTypes::MEI->value => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
            ProponentTypes::COLETIVO->value => ['sent' => $registrations_per_proponent_type, 'draft' => 3],
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
        foreach ($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // testar se cada comissão tem um total de 20 avaliações
        foreach (['committee 1', 'committee 2', 'committee 3'] as $committee_name) {
            $number_of_evaluations = $conn->fetchScalar("SELECT count(*) FROM evaluations WHERE valuer_committee = :committee", ['committee' => $committee_name]);

            $this->assertEquals($expected_total_per_committe, $number_of_evaluations, "[Avaliador repetido] Garantindo que o número de avaliações na comissão $committee_name está correto ($expected_total_per_committe)");
        }

        // testar se cada avaliador tem entre 4 e 5 avaliações
        foreach ($committees as $committee => $relations) {
            foreach ($relations as $relation) {
                $valuer_id = $relation->agent->id;

                $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id", ['valuer_id' => $valuer_id]);

                $this->assertGreaterThanOrEqual($min_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} tem ao menos {$min_evaluations_per_valuer} avaliações");
                $this->assertLessThanOrEqual($max_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} não tem mais que {$max_evaluations_per_valuer} avaliações");
            }
        }

        // testar se a soma de todos os sumários pendentes em cada comissão tem a soma de 20 avaliações
        $committee_total = [];
        foreach ($committees as $committee => $relations) {
            $committee_total = 0;
            foreach ($relations as $relation) {
                $committee_total += $relation->metadata['summary']['pending'];
            }

            $this->assertEquals($expected_total_per_committe, $committee_total, "[Avaliador repetido] Garantindo que a soma das inscrições pendentes dos resumos dos avaliadores da comissão $committee_name está correta");
        }
    }

    function testRangeFilteredRepeatedEvaluatorDistribution()
    {
        /*
         tendo 10 inscrições por categoria e tendo 2 avaliadores por comissão,
         cada comissão terá um total de 20 avaliações

         tendo 5 avaliadores únicos, que não são avaliadores de outras comissões,
         cada avaliador recebe 4 inscrições para avaliar.

         se há um avaliador que está em mais de uma comissão, cada avaliador recebe, no total 
         entre 4 e 5 avaliações


                      comissão 1   comissão 2    comissão 3      TOTAL DO AVALIADOR
        =========================+=============+==============+=====================
        avaliador 1       3           2                                  5              
        avaliador 2       4                                              4
        avaliador 3       4                                              4
        avaliador 4       5                                              5
        avaliador 5       4                                              4
        avaliador 6                   4                                  4
        avaliador 7                   5                                  5
        avaliador 8                   4                                  4
        avaliador 9                   5                                  5
        avaliador 10                                 4                   4
        avaliador 11                                 4                   4
        avaliador 12                                 4                   4
        avaliador 13                                 4                   4
        avaliador 14                                 4                   4
        ----------------------------------------------------------------------------
        TOTAL da                                                                           
        comissão         20          20             20                  60

        */

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 5;
        $registrations_per_range = 10;
        $valuers_per_registrations = 2;
        $min_evaluations_per_valuer = $registrations_per_range * $valuers_per_registrations / $valuers_per_committe;
        $max_evaluations_per_valuer = $min_evaluations_per_valuer + 1;
        $expected_total_per_committe = $registrations_per_range * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->addRange('Faixa 1')
            ->addRange('Faixa 2')
            ->addRange('Faixa 3')
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                ->setCommitteeFilterRange('committee 1', ['Faixa 1'])
                ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                ->setCommitteeFilterRange('committee 2', ['Faixa 2'])
                ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                ->setCommitteeFilterRange('committee 3', ['Faixa 3'])
                ->save()
                ->addValuers($valuers_per_committe - 1, 'committee 1')
                ->addValuers($valuers_per_committe - 1, 'committee 2')
                ->addValuers($valuers_per_committe, 'committee 3')
                ->addValuer('committee 1', 'fulano', $valuer->profile)->done()
                ->addValuer('committee 2', 'ciclano', $valuer->profile)->done()
                ->done()
            ->getInstance();

        // 30 inscrições enviadas sendo 10 por tipos de proponente
        $ranges = [
            'Faixa 1' => ['sent' => $registrations_per_range, 'draft' => 3],
            'Faixa 2' => ['sent' => $registrations_per_range, 'draft' => 3],
            'Faixa 3' => ['sent' => $registrations_per_range, 'draft' => 3],
        ];

        // Cria inscrições "enviadas" e "rascunho" para cada faixa especificada
        foreach ($ranges as $range => $counts) {
            $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations: $counts['sent'],
                range: $range
            );

            $this->registrationDirector->createDraftRegistrations(
                $opportunity,
                number_of_registrations: $counts['draft'],
                range: $range
            );
        }

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $committees = [];
        foreach ($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // testar se cada comissão tem um total de 20 avaliações
        foreach (['committee 1', 'committee 2', 'committee 3'] as $committee_name) {
            $number_of_evaluations = $conn->fetchScalar("SELECT count(*) FROM evaluations WHERE valuer_committee = :committee", ['committee' => $committee_name]);

            $this->assertEquals($expected_total_per_committe, $number_of_evaluations, "[Avaliador repetido] Garantindo que o número de avaliações na comissão $committee_name está correto ($expected_total_per_committe)");
        }

        // testar se cada avaliador tem entre 4 e 5 avaliações
        foreach ($committees as $committee => $relations) {
            foreach ($relations as $relation) {
                $valuer_id = $relation->agent->id;

                $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id", ['valuer_id' => $valuer_id]);

                $this->assertGreaterThanOrEqual($min_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} tem ao menos {$min_evaluations_per_valuer} avaliações");
                $this->assertLessThanOrEqual($max_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} não tem mais que {$max_evaluations_per_valuer} avaliações");
            }
        }

        // testar se a soma de todos os sumários pendentes em cada comissão tem a soma de 20 avaliações
        $committee_total = [];
        foreach ($committees as $committee => $relations) {
            $committee_total = 0;
            foreach ($relations as $relation) {
                $committee_total += $relation->metadata['summary']['pending'];
            }

            $this->assertEquals($expected_total_per_committe, $committee_total, "[Avaliador repetido] Garantindo que a soma das inscrições pendentes dos resumos dos avaliadores da comissão $committee_name está correta");
        }
    }

    function testFieldFilteredRepeatedEvaluatorDistribution()
    {
        /*
         tendo 10 inscrições por categoria e tendo 2 avaliadores por comissão,
         cada comissão terá um total de 20 avaliações

         tendo 5 avaliadores únicos, que não são avaliadores de outras comissões,
         cada avaliador recebe 4 inscrições para avaliar.

         se há um avaliador que está em mais de uma comissão, cada avaliador recebe, no total 
         entre 4 e 5 avaliações


                      comissão 1   comissão 2    comissão 3      TOTAL DO AVALIADOR
        =========================+=============+==============+=====================
        avaliador 1       3           2                                  5              
        avaliador 2       4                                              4
        avaliador 3       4                                              4
        avaliador 4       5                                              5
        avaliador 5       4                                              4
        avaliador 6                   4                                  4
        avaliador 7                   5                                  5
        avaliador 8                   4                                  4
        avaliador 9                   5                                  5
        avaliador 10                                 4                   4
        avaliador 11                                 4                   4
        avaliador 12                                 4                   4
        avaliador 13                                 4                   4
        avaliador 14                                 4                   4
        ----------------------------------------------------------------------------
        TOTAL da                                                                           
        comissão         20          20             20                  60

        */

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $valuer = $this->userDirector->createUser();

        $valuers_per_committe = 5;
        $registrations_per_field = 10;
        $valuers_per_registrations = 2;
        $min_evaluations_per_valuer = $registrations_per_field * $valuers_per_registrations / $valuers_per_committe;
        $max_evaluations_per_valuer = $min_evaluations_per_valuer + 1;
        $expected_total_per_committe = $registrations_per_field * $valuers_per_registrations;

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa')
                ->createField('cor', 'select', required:true, options:['Azul', 'Vermelho', 'Amarelo'])
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registrations)
                ->setCommitteeFilterField('committee 1', 'cor', ['Azul'])
                ->setCommitteeValuersPerRegistration('committee 2', $valuers_per_registrations)
                ->setCommitteeFilterField('committee 2', 'cor', ['Vermelho'])
                ->setCommitteeValuersPerRegistration('committee 3', $valuers_per_registrations)
                ->setCommitteeFilterField('committee 3', 'cor', ['Amarelo'])
                ->save()
                ->addValuers($valuers_per_committe - 1, 'committee 1')
                ->addValuers($valuers_per_committe - 1, 'committee 2')
                ->addValuers($valuers_per_committe, 'committee 3')
                ->addValuer('committee 1', 'fulano', $valuer->profile)->done()
                ->addValuer('committee 2', 'ciclano', $valuer->profile)->done()
                ->done()
            ->getInstance();

        // 30 inscrições enviadas sendo 10 por tipos de proponente
        $fields = [
            'Azul' => ['sent' => $registrations_per_field],
            'Vermelho' => ['sent' => $registrations_per_field],
            'Amarelo' => ['sent' => $registrations_per_field],
        ];

        $field_cor = $this->opportunityBuilder->getFieldName('cor');

        // Cria inscrições "enviadas" para cada faixa especificada
        foreach ($fields as $cor => $counts) {
            $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations: $counts['sent'],
                data: [$field_cor => $cor]
            );
        }

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $committees = [];
        foreach ($emc->agentRelations as $relation) {
            $committees[$relation->group][] = $relation;
        }

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        
        // testar se cada comissão tem um total de 20 avaliações
        foreach (['committee 1', 'committee 2', 'committee 3'] as $committee_name) {
            $number_of_evaluations = $conn->fetchScalar("SELECT count(*) FROM evaluations WHERE valuer_committee = :committee", ['committee' => $committee_name]);

            $this->assertEquals($expected_total_per_committe, $number_of_evaluations, "[Avaliador repetido] Garantindo que o número de avaliações na comissão $committee_name está correto ($expected_total_per_committe)");
        }

        // testar se cada avaliador tem entre 4 e 5 avaliações
        foreach ($committees as $committee => $relations) {
            foreach ($relations as $relation) {
                $valuer_id = $relation->agent->id;

                $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id", ['valuer_id' => $valuer_id]);

                $this->assertGreaterThanOrEqual($min_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} tem ao menos {$min_evaluations_per_valuer} avaliações");
                $this->assertLessThanOrEqual($max_evaluations_per_valuer, $number_of_evaluations, "[Avaliador repetido] Garantindo que o avaliador da comissão {$committee} não tem mais que {$max_evaluations_per_valuer} avaliações");
            }
        }

        // testar se a soma de todos os sumários pendentes em cada comissão tem a soma de 20 avaliações
        $committee_total = [];
        foreach ($committees as $committee => $relations) {
            $committee_total = 0;
            foreach ($relations as $relation) {
                $committee_total += $relation->metadata['summary']['pending'];
            }

            $this->assertEquals($expected_total_per_committe, $committee_total, "[Avaliador repetido] Garantindo que a soma das inscrições pendentes dos resumos dos avaliadores da comissão $committee_name está correta");
        }
    }

    function testDistributionConfigurationHourly()
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

        /** @var EvaluationMethodConfiguration $emc */
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        // Define a configuração de distribuição como 'hourly'
        $emc->distributionConfiguration = 'hourly';
        $emc->save(true);

        // Gera o ID do job
        $job_type = $this->app->getRegisteredJobType('RedistribRegs');
        $job_id = $job_type->generateId(
            ['evaluationMethodConfiguration' => $emc],
            'now',
            '',
            1
        );
        
        /** @var \MapasCulturais\Entities\Job */
        $job = $this->app->repo('Job')->findOneBy(['id' => $job_id]);
        
        $this->assertNotNull($job, 'Garantindo que o job de redistribuição foi agendado');
        
        // Verifica que o job foi agendado para a próxima hora
        $expected_execution_time = DateTime::date('Y-m-d H:00:00', strtotime('+1 hour'));
        $actual_execution_time = $job->nextExecutionTimestamp->format('Y-m-d H:i:s');
        $this->assertEquals($expected_execution_time, $actual_execution_time, 'Garantindo que o job foi agendado para a próxima hora');

       
        $emc->redistributeCommitteeRegistrations();

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

    function testDistributionConfigurationDaily()
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

        /** @var EvaluationMethodConfiguration $emc */
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        // Define a configuração de distribuição como 'hourly'
        $emc->distributionConfiguration = 'daily';
        $emc->save(true);

        // Gera o ID do job
        $job_type = $this->app->getRegisteredJobType('RedistribRegs');
        $job_id = $job_type->generateId(
            ['evaluationMethodConfiguration' => $emc],
            'now',
            '',
            1
        );
        
        /** @var \MapasCulturais\Entities\Job */
        $job = $this->app->repo('Job')->findOneBy(['id' => $job_id]);
        
        $this->assertNotNull($job, 'Garantindo que o job de redistribuição foi agendado');
        
        // Verifica que o job foi agendado para a próxima meia-noite
        $next_midnight = new DateTime('tomorrow 00:00:00');
        $expected_execution_time = $next_midnight->format('Y-m-d H:i:s');
        $actual_execution_time = $job->nextExecutionTimestamp->format('Y-m-d H:i:s');
        $this->assertEquals($expected_execution_time, $actual_execution_time, 'Garantindo que o job foi agendado para a próxima meia-noite');
       
        $emc->redistributeCommitteeRegistrations();

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

    function testDistributionConfigurationDeactivate()
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

        /** @var EvaluationMethodConfiguration $emc */
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $emc->distributionConfiguration = 'deactivate';
        $emc->save(true);

        $job_type = $this->app->getRegisteredJobType('RedistribRegs');
        $job_id = $job_type->generateId(
            ['evaluationMethodConfiguration' => $emc],
            'now',
            '1 hour',
            1
        );
        
        /** @var \MapasCulturais\Entities\Job */
        $job = $this->app->repo('Job')->findOneBy(['id' => $job_id]);
        
        $this->assertNull($job, 'Garantindo que o job de redistribuição foi removido ou não foi enfileirado');

        $emc = $emc->refreshed();

        $valuer1_summary = $emc->agentRelations[0]->metadata->summary;
        $valuer2_summary = $emc->agentRelations[1]->metadata->summary;

        $this->assertEquals(0, $valuer1_summary['pending'] ?? 0, 'Garantindo que o avaliador 1 não tem avaliações pendentes');
        $this->assertEquals(0, $valuer2_summary['pending'] ?? 0, 'Garantindo que o avaliador 2 não tem avaliações pendentes');

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");

        $this->assertEquals(0, $number_of_evaluations, 'Garantindo que não tenha avaliações');
    }

    function testMaxRegistrationsPerValuerLimit()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $number_of_registrations = 20;
        $fulano_max_registrations = 3;
        $beltrano_max_registrations = 5;

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->createSentRegistrations($number_of_registrations)
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuer('committee 1', name: 'fulano')
                    ->maxRegistrations($fulano_max_registrations)
                    ->done()
                ->addValuer('committee 1', name: 'ciclano')
                    ->done()
                ->addValuer('committee 1', name: 'beltrano')
                    ->maxRegistrations($beltrano_max_registrations)
                    ->done()
                ->redistributeCommitteeRegistrations()
                ->done()
            ->getInstance();


        /** @var EvaluationMethodConfigurationAgentRelation[] */
        $valuers = $opportunity->evaluationMethodConfiguration->agentRelations;

        $fulano = $valuers[0];
        $ciclano = $valuers[1];
        $beltrano = $valuers[2];

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // Verifica se o fulano recebeu o número correto de avaliações 
        
        $fulano_evaluations = $conn->fetchScalar(
            "SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $fulano->agent->id]
        );
        $this->assertEquals($fulano_max_registrations, $fulano_evaluations, 'Garantindo que o primeiro avaliador com limite de inscrições na comissão recebeu o número correto de inscrições');

        // Verifica se o beltrano recebeu no máximo 10 inscrições
        $beltrano_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $beltrano->agent->id]
        );
        $this->assertEquals($beltrano_max_registrations, $beltrano_evaluations, 'Garantindo que o segundo avaliador com limite de inscrições na comissão recebeu o número correto de inscrições');

        // Verifica se o ciclano recebeu as demais avaliações (Total de inscrições - avaliações do fulano - avaliações do beltrano)
        $ciclano_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $ciclano->agent->id]
        );

        $this->assertEquals(
            expected: $number_of_registrations - $fulano_evaluations - $beltrano_evaluations, 
            actual: $ciclano_evaluations, 
            message: 'Garantindo que o avaliador SEM limite de inscrições na comissão recebeu as demais inscrições'
        );

        // Verifica se o total de avaliações é 20 (todas as inscrições foram distribuídas)
        $total_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");
        $this->assertEquals($number_of_registrations, $total_evaluations, 'Garantindo que todas as inscrições foram distribuídas');
        
        $this->assertEquals($fulano_max_registrations, $fulano->maxRegistrations, 'Garantindo que o getter retorna o valor correto para o primeiro avaliador com limite');
        $this->assertEquals($beltrano_max_registrations, $beltrano->maxRegistrations, 'Garantindo que o getter retorna o valor correto para o segundo avaliador com limite');
    }

    function testReplaceEvaluatorTransfersEvaluations() {
        $app = App::i();
        
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
    
        // Criar oportunidade com 2 avaliadores e diferentes tipos de avaliações
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->createSentRegistrations(number_of_registrations: 8)
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 2)
                ->save()
                ->addValuer('committee 1', name: 'fulano')
                    ->done()
                ->addValuer('committee 1', name: 'ciclano')
                    ->done()
                ->redistributeCommitteeRegistrations()
                ->withValuer('committee 1', 'fulano')
                    ->createDraftEvaluation()
                    ->createDraftEvaluation()
                    ->createSentEvaluation()
                    ->createSentEvaluation()
                    ->createConcludedEvaluation()
                    ->done()
                ->withValuer('committee 1', 'ciclano')
                    ->createDraftEvaluation()
                    ->createDraftEvaluation()
                    ->createSentEvaluation()
                    ->createSentEvaluation()
                    ->done()
                ->done()
            ->getInstance();
    
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $old_evaluator_relation = $emc->agentRelations[0];
        $old_evaluator_user_id = $old_evaluator_relation->agent->user->id;
        $opportunityId = $opportunity->id;
    
        // Query SQL para buscar inscrições com avaliações pendentes/iniciadas/enviadas/concluídas
        $base_query = "
            SELECT registration_id 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND
                valuer_user_id = :valuer_user_id";
        
        $params = [
            'valuer_user_id' => $old_evaluator_user_id,
            'opportunityId' => $opportunityId,
            'status' => EvaluationMethodConfigurationAgentRelation::STATUS_ACTIVE
        ];
        
        $query_pending_evaluations_before = $base_query . " AND (evaluation_status IS NULL OR evaluation_status < :status)";
        $pending_registration_before = $app->conn->fetchColumn($query_pending_evaluations_before, $params);

        $query_started_or_sent_evaluations_before = $base_query . " AND evaluation_status >= :status";
        $started_or_sent_registrations_before = $app->conn->fetchColumn($query_started_or_sent_evaluations_before, $params);
    
        $newEvaluator = $this->userDirector->createUser();

        $new_evaluatior_relation = $old_evaluator_relation->replaceEvaluator($newEvaluator);
        
        // 1. Verificar se o avaliador antigo foi desabilitado
        $this->assertEquals(
            EvaluationMethodConfigurationAgentRelation::STATUS_DISABLED,
            $old_evaluator_relation->status,
            'Garantindo que o antigo avaliador foi desabilitado'
        );

        // 2. Verificar que as avaliações concluídas/enviadas do avaliador antigo ainda estão com ele (não foram transferidas)
        $params['valuer_user_id'] = $old_evaluator_user_id;
        $query_started_or_sent_evaluations_old_after = $base_query . " AND evaluation_status >= :status";
        $started_or_sent_registrations_old_after = $app->conn->fetchColumn($query_started_or_sent_evaluations_old_after, $params);

        $this->assertEquals(
            $started_or_sent_registrations_before,
            $started_or_sent_registrations_old_after,
            'Garantindo que as inscrições com avaliações concluídas/enviadas do avaliador antigo permanecem com ele (não foram transferidas)'
        );

        // 3. Verificar que o novo avaliador não tem essas avaliações concluídas/enviadas
        $params['valuer_user_id'] = $new_evaluatior_relation->agent->user->id;
        $registration_ids = implode(',', $started_or_sent_registrations_before);
        
        $query_started_or_sent_evaluations_new = $base_query . " AND evaluation_status >= :status AND registration_id IN ({$registration_ids})";
        $started_or_sent_registrations_new = $app->conn->fetchColumn($query_started_or_sent_evaluations_new, $params);

        $this->assertEquals(
            [],
            $started_or_sent_registrations_new,
            'Garantindo que o novo avaliador não recebeu as avaliações concluídas/enviadas do avaliador antigo'
        );

        // 4. Garantindo que as inscrições com avaliações pendentes/iniciadas do avaliador antigo foram transferidas para o novo avaliador
        $params['valuer_user_id'] = $new_evaluatior_relation->agent->user->id;
        $query_pending_evaluations_after = $base_query . " AND (evaluation_status IS NULL OR evaluation_status < :status)";
        $pending_registration_after = $app->conn->fetchColumn($query_pending_evaluations_after, $params);
        
        $this->assertEquals(
            $pending_registration_before,
            $pending_registration_after,
            'Garantindo que as inscrições com avaliações pendentes/iniciadas do avaliador antigo foram transferidas para o novo avaliador'
        );
    }

    function testCommitteeFilterByRegistrationSentTimestamp()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        
        $day1_num_registrations = 2;
        $day1_create = new DateTime('-5 days 12:00');
        $day1_sent = new DateTime('-5 days 13:00');

        $day2_num_registrations = 4;
        $day2_create = new DateTime('-4 days 12:00');
        $day2_sent = new DateTime('-4 days 13:00');

        $day3_num_registrations = 3;
        $day3_create = new DateTime('-3 days 12:00');
        $day3_sent = new DateTime('-3 days 13:00');

        $day4_num_registrations = 5;
        $day4_create = new DateTime('-2 days 12:00');
        $day4_sent = new DateTime('-2 days 13:00');

        $day1 = '-5 days';
        $day2 = '-4 days';
        $day3 = '-3 days';
        $day4 = '-2 days';
        
        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->done()
            ->save()
            ->createSentRegistrations(
                number_of_registrations: $day1_num_registrations,
                data: ['createTimestamp' => $day1_create, 'sentTimestamp' => $day1_sent]
            )
            ->createSentRegistrations(
                number_of_registrations: $day2_num_registrations,
                data: ['createTimestamp' => $day2_create, 'sentTimestamp' => $day2_sent]
            )
            ->createSentRegistrations(
                number_of_registrations: $day3_num_registrations,
                data: ['createTimestamp' => $day3_create, 'sentTimestamp' => $day3_sent]
            )
            ->createSentRegistrations(
                number_of_registrations: $day4_num_registrations,
                data: ['createTimestamp' => $day4_create, 'sentTimestamp' => $day4_sent]
            )
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)

                // menbros dessa comissão avaliam somente inscrições do dia 1
                ->setCommitteeValuersPerRegistration('day 1', 1)
                ->setCommitteeFilterBySentTimestamp('day 1', from_datetime: "$day1 00:00:00", to_datetime: "$day1 23:59:59")

                // menbros dessa comissão avaliam somente inscrições do dia 2
                ->setCommitteeValuersPerRegistration('day 2', 1)
                ->setCommitteeFilterBySentTimestamp('day 2', from_datetime: "$day2 00:00:00", to_datetime: "$day2 23:59:59")

                // // menbros dessa comissão avaliam inscrições do dia 3 e do dia 4
                ->setCommitteeValuersPerRegistration('from day 3', 1)
                ->setCommitteeFilterBySentTimestamp('from day 3', from_datetime: "$day3 00:00:00")

                // // menbros dessa comissão avaliam inscrições do dia 1 e do dia 2
                ->setCommitteeValuersPerRegistration('until day 3', 1)
                ->setCommitteeFilterBySentTimestamp('until day 3', to_datetime: "$day3 23:59:59")

                // // menbros dessa comissão avaliam inscrições do dia 1 e do dia 2
                ->setCommitteeValuersPerRegistration('until day 4', 2)
                ->setCommitteeFilterBySentTimestamp('until day 4', to_datetime: "$day4 23:59:59")

                ->save()

                ->addValuer('day 1', name: 'valuer day 1')->done()
                ->addValuer('day 2', name: 'valuer 1 - day 2')->done()
                ->addValuer('day 2', name: 'valuer 2 - day 2')->done()
                ->addValuer('from day 3', name: 'valuer from day 3')->done()
                ->addValuer('until day 3', name: 'valuer 1 - until day 3')->done()
                ->addValuer('until day 4', name: 'valuer 1 - until day 4')->done()
                ->addValuer('until day 4', name: 'valuer 2 - until day 4')->done()

                ->redistributeCommitteeRegistrations()

                ->done();

        $opportunity = $this->opportunityBuilder->getInstance();
        $evaluation_phase = $opportunity->evaluationMethodConfiguration;

        $conn = $this->app->conn;

        // Verifica que a comissão day 1 tem o número correto de inscições
        $day1_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND 
                valuer_committee = :committee",
            [
                'opportunityId' => $opportunity->id,
                'committee' => 'day 1'
            ]
        );
        $this->assertEquals($day1_num_registrations, $day1_evaluations, "Garantindo que a comissão 'day 1' possui o número correto de avaliações");

        
        // Verifica que a comissão day 2 tem o número correto de inscições
        $day2_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND 
                valuer_committee = :committee",
            [
                'opportunityId' => $opportunity->id,
                'committee' => 'day 2'
            ]
        );
        $this->assertEquals($day2_num_registrations, $day2_evaluations, "Garantindo que a comissão 'day 2' possui o número correto de avaliações");


        // Verifica que a comissão day 2 tem o número correto de inscições
        $from_day3_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND 
                valuer_committee = :committee",
            [
                'opportunityId' => $opportunity->id,
                'committee' => 'from day 3'
            ]
        );
        $expected = $day3_num_registrations + $day4_num_registrations;
        $this->assertEquals($expected, $from_day3_evaluations, "Garantindo que a comissão 'from day 3' possui o número correto de avaliações");


        // Verifica que a comissão day 2 tem o número correto de inscições
        $until_day3_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND 
                valuer_committee = :committee",
            [
                'opportunityId' => $opportunity->id,
                'committee' => 'until day 3'
            ]
        );
        $expected = $day1_num_registrations + $day2_num_registrations + $day3_num_registrations;
        $this->assertEquals($expected, $until_day3_evaluations, "Garantindo que a comissão 'until day 3' possui o número correto de avaliações");


        // Verifica que a comissão day 2 tem o número correto de inscições
        $until_day4_evaluations = $conn->fetchScalar(  
            "SELECT COUNT(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opportunityId AND 
                valuer_committee = :committee",
            [
                'opportunityId' => $opportunity->id,
                'committee' => 'until day 4'
            ]
        );
        $expected = ($day1_num_registrations + $day2_num_registrations + $day3_num_registrations + $day4_num_registrations) * 2;
        $this->assertEquals($expected, $until_day4_evaluations, "Garantindo que a comissão 'until day 4' possui o número correto de avaliações");
    }

    function testValuerRegistrationListInclusive()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $number_of_registrations = 20;

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->createSentRegistrations($number_of_registrations);

        // pega listas de inscrições já enviadas
        $opportunity = $this->opportunityBuilder->getInstance();
        $sent_registrations = $opportunity->getSentRegistrations();
        $fulano_registration_numbers = array_slice(array_map(fn($reg) => $reg->number, $sent_registrations), 0, 5);
        $beltrano_registration_numbers = array_slice(array_map(fn($reg) => $reg->number, $sent_registrations), 5, 5);

        // adiciona fase e avaliadores já com as listas inclusivas configuradas
        $opportunity = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuer('committee 1', name: 'fulano')
                    ->registrationList($fulano_registration_numbers, false)
                    ->done()
                ->addValuer('committee 1', name: 'ciclano')
                    ->done()
                ->addValuer('committee 1', name: 'beltrano')
                    ->registrationList($beltrano_registration_numbers, false)
                    ->done()
                ->redistributeCommitteeRegistrations()
                ->done()
            ->getInstance();

        /** @var EvaluationMethodConfigurationAgentRelation[] */
        $valuers = $opportunity->evaluationMethodConfiguration->agentRelations;

        $fulano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'fulano'))[0];
        $ciclano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'ciclano'))[0];
        $beltrano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'beltrano'))[0];

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // Verifica se o fulano recebeu pelo menos as 5 inscrições da lista
        $fulano_evaluations = $conn->fetchAll(
            "SELECT registration_id FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $fulano->agent->id]
        );
        
        $fulano_registration_ids = array_column($fulano_evaluations, 'registration_id');
        
        // Obtém os IDs das inscrições da lista do fulano
        $fulano_list_registration_ids = array_map(
            fn($number) => $this->app->repo('Registration')->findOneBy(['number' => $number])->id,
            $fulano_registration_numbers
        );

        // Verifica se todas as inscrições da lista foram atribuídas ao fulano
        foreach ($fulano_list_registration_ids as $registration_id) {
            $this->assertContains(
                $registration_id,
                $fulano_registration_ids,
                'Garantindo que todas as inscrições da lista foram atribuídas ao avaliador fulano'
            );
        }

        // Verifica se o beltrano recebeu as 5 inscrições da lista
        $beltrano_evaluations = $conn->fetchAll(
            "SELECT registration_id FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $beltrano->agent->id]
        );
        
        $beltrano_registration_ids = array_column($beltrano_evaluations, 'registration_id');
        
        // Obtém os IDs das inscrições da lista do beltrano
        $beltrano_list_registration_ids = array_map(
            fn($number) => $this->app->repo('Registration')->findOneBy(['number' => $number])->id,
            $beltrano_registration_numbers
        );

        // Verifica se todas as inscrições da lista foram atribuídas ao beltrano
        foreach ($beltrano_list_registration_ids as $registration_id) {
            $this->assertContains(
                $registration_id,
                $beltrano_registration_ids,
                'Garantindo que todas as inscrições da lista foram atribuídas ao avaliador beltrano'
            );
        }

        // Verifica se o total de avaliações é 20 (todas as inscrições foram distribuídas)
        $total_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");
        $this->assertEquals($number_of_registrations, $total_evaluations, 'Garantindo que todas as inscrições foram distribuídas');

        // Verifica se o ciclano recebeu as demais avaliações (Total de inscrições - avaliações do fulano - avaliações do beltrano)
        $ciclano_evaluations = $conn->fetchScalar(
            "SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $ciclano->agent->id]
        );

        $fulano_evaluations_count = count($fulano_evaluations);
        $beltrano_evaluations_count = count($beltrano_evaluations);

        $this->assertEquals(
            expected: $number_of_registrations - $fulano_evaluations_count - $beltrano_evaluations_count, 
            actual: $ciclano_evaluations, 
            message: 'Garantindo que o avaliador sem lista de inscrições na comissão recebeu as demais inscrições'
        );
    }

    function testValuerRegistrationListExclusive()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $number_of_registrations = 20;

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->createSentRegistrations($number_of_registrations);

        // pega listas de inscrições já enviadas
        $sent_registrations = $this->opportunityBuilder->getInstance()->getSentRegistrations();
        $fulano_registration_numbers = array_slice(array_map(fn($reg) => $reg->number, $sent_registrations), 0, 5);
        $beltrano_registration_numbers = array_slice(array_map(fn($reg) => $reg->number, $sent_registrations), 5, 5);

        // adiciona fase e avaliadores com listas exclusivas configuradas
        $opportunity = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuer('committee 1', name: 'fulano')
                    ->registrationList($fulano_registration_numbers, true)
                    ->done()
                ->addValuer('committee 1', name: 'ciclano')
                    ->done()
                ->addValuer('committee 1', name: 'beltrano')
                    ->registrationList($beltrano_registration_numbers, true)
                    ->done()
                ->redistributeCommitteeRegistrations()
                ->done()
            ->getInstance();

        /** @var EvaluationMethodConfigurationAgentRelation[] */
        $valuers = $opportunity->evaluationMethodConfiguration->agentRelations;

        $fulano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'fulano'))[0];
        $ciclano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'ciclano'))[0];
        $beltrano = array_values(array_filter($valuers, fn($relation) => $relation->agent->name == 'beltrano'))[0];

        /** @var Connection */
        $conn = $this->app->em->getConnection();

        // Fulano deve receber exatamente as 5 inscrições da lista (exclusivo)
        $fulano_evaluations = $conn->fetchAll(
            "SELECT registration_id FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $fulano->agent->id]
        );
        $fulano_registration_ids = array_column($fulano_evaluations, 'registration_id');
        $fulano_list_registration_ids = array_map(
            fn($number) => $this->app->repo('Registration')->findOneBy(['number' => $number])->id,
            $fulano_registration_numbers
        );
        foreach ($fulano_list_registration_ids as $registration_id) {
            $this->assertContains(
                $registration_id,
                $fulano_registration_ids,
                'Garantindo que todas as inscrições da lista foram atribuídas ao avaliador fulano (exclusivo)'
            );
        }
        $this->assertCount(5, $fulano_registration_ids, 'Fulano deve receber exatamente as inscrições da lista (exclusivo)');

        // Beltrano deve receber exatamente as 5 inscrições da lista (exclusivo)
        $beltrano_evaluations = $conn->fetchAll(
            "SELECT registration_id FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $beltrano->agent->id]
        );
        $beltrano_registration_ids = array_column($beltrano_evaluations, 'registration_id');
        $beltrano_list_registration_ids = array_map(
            fn($number) => $this->app->repo('Registration')->findOneBy(['number' => $number])->id,
            $beltrano_registration_numbers
        );
        foreach ($beltrano_list_registration_ids as $registration_id) {
            $this->assertContains(
                $registration_id,
                $beltrano_registration_ids,
                'Garantindo que todas as inscrições da lista foram atribuídas ao avaliador beltrano (exclusivo)'
            );
        }
        $this->assertCount(5, $beltrano_registration_ids, 'Beltrano deve receber exatamente as inscrições da lista (exclusivo)');

        // Ciclano recebe as demais 10
        $ciclano_evaluations = $conn->fetchScalar(
            "SELECT COUNT(*) FROM evaluations WHERE valuer_agent_id = :valuer_id",
            ['valuer_id' => $ciclano->agent->id]
        );
        $this->assertEquals(
            10,
            $ciclano_evaluations,
            'Garantindo que o avaliador sem lista recebeu as demais inscrições'
        );

        // Total distribuído deve ser 20
        $total_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");
        $this->assertEquals($number_of_registrations, $total_evaluations, 'Garantindo que todas as inscrições foram distribuídas');
    }
}
