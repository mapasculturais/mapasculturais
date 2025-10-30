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
                ->addValuer('committee 1', $valuer->profile)
                ->addValuer('committee 2', $valuer->profile)
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
                ->addValuer('committee 1', $valuer->profile)
                ->addValuer('committee 2', $valuer->profile)
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
                ->addValuer('committee 1', $valuer->profile)
                ->addValuer('committee 2', $valuer->profile)
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
                ->addValuer('committee 1', $valuer->profile)
                ->addValuer('committee 2', $valuer->profile)
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
}
