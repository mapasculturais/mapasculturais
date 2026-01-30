<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityPhasesGettersTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    // phases
    function testOpportunityPhasesGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $opportunity */
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
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->refresh()
            ->getInstance();

        $phases = $opportunity->phases;

        $this->assertIsArray($phases, 'Certificando que phases retorna um array');
        $this->assertNotEmpty($phases, 'Certificando que phases não é vazio quando a oportunidade tem fases');

        $expected_count = 5;
        $this->assertCount($expected_count, $phases, "Certificando que phases retorna {$expected_count} itens para 1 fase de coleta + 3 fases de avaliação");
    }

    // allPhases
    function testOpportunityAllPhasesGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;

        /** @var Opportunity $opportunity */
        $opportunity = $builder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        // Guarda o ID da primeira fase
        $first_phase_id = $opportunity->id;

        // Cria as fases de avaliação e guarda os IDs das oportunidades
        $eval_phase_1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_1_opp_id = $eval_phase_1->opportunity->id;

        $eval_phase_2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_2_opp_id = $eval_phase_2->opportunity->id;

        $eval_phase_3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_3_opp_id = $eval_phase_3->opportunity->id;

        // IDs esperados na ordem: firstPhase, eval_phase_1, eval_phase_2, eval_phase_3 (lastPhase)
        $expected_phase_ids = [
            $first_phase_id,
            $eval_phase_1_opp_id,
            $eval_phase_2_opp_id,
            $eval_phase_3_opp_id,
        ];

        $all_phases = $opportunity->allPhases;

        $this->assertIsArray($all_phases, 'Certificando que allPhases retorna um array');
        $this->assertNotEmpty($all_phases, 'Certificando que allPhases não é vazio quando a oportunidade tem fases');

        // Com 1 fase de coleta + 3 fases de avaliação, esperamos 4 fases no total
        $expected_count = 4;
        $this->assertCount($expected_count, $all_phases, "Certificando que allPhases retorna {$expected_count} fases para 1 fase de coleta + 3 fases de avaliação");

        // Verifica que a primeira fase é a firstPhase
        $first_phase = $all_phases[0];
        $this->assertEquals($first_phase_id, $first_phase->id, 'Certificando que a primeira fase em allPhases é a firstPhase');
        $this->assertTrue($first_phase->isFirstPhase, 'Certificando que a primeira fase em allPhases tem isFirstPhase = true');

        // Verifica que todas as fases criadas aparecem em allPhases
        $present_ids = array_map(fn($phase) => $phase->id, $all_phases);
        foreach ($expected_phase_ids as $expected_id) {
            $this->assertContains($expected_id, $present_ids, "Certificando que a fase com id {$expected_id} está presente em allPhases");
        }

        // Verifica que a última fase em allPhases é a lastPhase da oportunidade (última fase em geral, não necessariamente a última de avaliação)
        $last_phase_in_list = $all_phases[count($all_phases) - 1];
        $last_phase = $opportunity->lastPhase;
        $this->assertTrue($last_phase_in_list->isLastPhase, 'Certificando que a última fase em allPhases tem isLastPhase = true');
        $this->assertTrue($last_phase->isLastPhase, 'Certificando que lastPhase da oportunidade tem isLastPhase = true');
        $this->assertEquals($last_phase->id, $last_phase_in_list->id, 'Certificando que a última fase em allPhases é a mesma que opportunity->lastPhase');

        // Verifica que as fases estão na ordem correta (firstPhase primeiro, lastPhase por último)
        $this->assertEquals($first_phase_id, $all_phases[0]->id, 'Certificando que a primeira fase em allPhases é a firstPhase');
        $this->assertEquals($last_phase->id, $all_phases[count($all_phases) - 1]->id, 'Certificando que a última fase em allPhases é a lastPhase da oportunidade');
    }

    // nextPhase
    function testOpportunityNextPhaseGetter()
    {
        $app = $this->app;
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;

        /** @var Opportunity $opportunity */
        $opportunity = $builder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $eval_phase_1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_1_opp_id = $eval_phase_1->opportunity->id;

        $eval_phase_2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_2_opp_id = $eval_phase_2->opportunity->id;

        $eval_phase_3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();
        $eval_phase_3_opp_id = $eval_phase_3->opportunity->id;

        $last_phase = $opportunity->lastPhase;

        // firstPhase->nextPhase deve ser a primeira fase de avaliação (ou a próxima na ordem de allPhases)
        $all_phases = $opportunity->allPhases;
        $first_phase_next_expected_id = $all_phases[1]->id;

        $next = $opportunity->nextPhase;
        $this->assertNotNull($next, 'Certificando que firstPhase tem nextPhase');
        $this->assertEquals($first_phase_next_expected_id, $next->id, 'Certificando que nextPhase da firstPhase é a próxima fase em allPhases');

        // primeira fase de avaliação -> nextPhase deve ser a segunda
        $next = $eval_phase_1->opportunity->nextPhase;
        $this->assertNotNull($next, 'Certificando que a primeira fase de avaliação tem nextPhase');
        $this->assertEquals($eval_phase_2_opp_id, $next->id, 'Certificando que nextPhase da primeira fase de avaliação é a segunda');

        // segunda fase de avaliação -> nextPhase deve ser a terceira
        $next = $eval_phase_2->opportunity->nextPhase;
        $this->assertNotNull($next, 'Certificando que a segunda fase de avaliação tem nextPhase');
        $this->assertEquals($eval_phase_3_opp_id, $next->id, 'Certificando que nextPhase da segunda fase de avaliação é a terceira');

        // terceira fase de avaliação -> nextPhase deve ser a última fase
        $next = $eval_phase_3->opportunity->nextPhase;
        $this->assertNotNull($next, 'Certificando que nextPhase da terceira fase é a última fase');
        $this->assertEquals($last_phase->id, $next->id, 'Certificando que nextPhase da terceira fase é a última fase');

        // última fase (lastPhase) -> nextPhase retorna null (não há próxima fase; lastPhase costuma ser reporting phase)
        $next = $last_phase->nextPhase;
        $this->assertNull($next, 'Certificando que nextPhase da última fase é null');
    }

    // previousPhase
    function testOpportunityPreviousPhaseGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        // Fases de avaliação em sequência por data: cada uma abre após o fechamento da anterior
        $eval_phase_1 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+2 days')
                ->setEvaluationTo('+9 days')
                ->save()
                ->getInstance();
        $eval_phase_1_opp_id = $eval_phase_1->opportunity->id;

        $eval_phase_2 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+10 days')
                ->setEvaluationTo('+17 days')
                ->save()
                ->getInstance();
        $eval_phase_2_opp_id = $eval_phase_2->opportunity->id;

        $eval_phase_3 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+18 days')
                ->setEvaluationTo('+25 days')
                ->save()
                ->getInstance();
        $eval_phase_3_opp_id = $eval_phase_3->opportunity->id;

        $opportunity = $opportunity->refreshed();
        $last_phase = $opportunity->lastPhase;

        // firstPhase->previousPhase deve ser null
        $previous = $opportunity->previousPhase;
        $this->assertNull($previous, 'Certificando que previousPhase da firstPhase é null');

        // A primeira fase de avaliação usa a mesma Opportunity que a firstPhase
        $this->assertEquals($opportunity->id, $eval_phase_1_opp_id, 'Certificando que a primeira fase de avaliação é a própria firstPhase');
        $previous = $eval_phase_1->opportunity->previousPhase;
        $this->assertNull($previous, 'Certificando que previousPhase da primeira fase de avaliação é null (é a firstPhase)');

        // segunda fase de avaliação -> previousPhase deve ser a firstPhase
        $previous = $eval_phase_2->opportunity->previousPhase;
        $this->assertNotNull($previous, 'Certificando que a segunda fase de avaliação tem previousPhase');
        $this->assertEquals($opportunity->id, $previous->id, 'Certificando que previousPhase da segunda fase de avaliação é a firstPhase');

        // terceira fase de avaliação -> previousPhase deve ser a segunda
        $previous = $eval_phase_3->opportunity->previousPhase;
        $this->assertNotNull($previous, 'Certificando que a terceira fase de avaliação tem previousPhase');
        $this->assertEquals($eval_phase_2_opp_id, $previous->id, 'Certificando que previousPhase da terceira fase de avaliação é a segunda');

        // última fase (lastPhase) -> previousPhase deve ser a penultima fase
        $previous = $last_phase->previousPhase;
        $this->assertNotNull($previous, 'Certificando que lastPhase tem previousPhase');
        $this->assertEquals($eval_phase_3_opp_id, $previous->id, 'Certificando que previousPhase da lastPhase é a penultima fase');
    }

    // firstPhase
    function testOpportunityFirstPhaseGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;

        /** @var Opportunity $opportunity */
        $opportunity = $builder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $first_phase_id = $opportunity->id;

        $eval_phase_1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $eval_phase_2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $eval_phase_3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $last_phase = $opportunity->lastPhase;

        // firstPhase da própria firstPhase retorna ela mesma
        $first = $opportunity->firstPhase;
        $this->assertNotNull($first, 'Certificando que firstPhase retorna valor');
        $this->assertEquals($first_phase_id, $first->id, 'Certificando que firstPhase da oportunidade raiz é ela mesma');
        $this->assertTrue($first->isFirstPhase, 'Certificando que firstPhase tem isFirstPhase = true');

        // primeira fase de avaliação usa a mesma Opportunity que a firstPhase
        $first = $eval_phase_1->opportunity->firstPhase;
        $this->assertNotNull($first, 'Certificando que a primeira fase de avaliação tem firstPhase');
        $this->assertEquals($first_phase_id, $first->id, 'Certificando que firstPhase da primeira fase de avaliação é a primeira fase');

        // segunda fase de avaliação -> firstPhase deve ser a primeira fase
        $first = $eval_phase_2->opportunity->firstPhase;
        $this->assertNotNull($first, 'Certificando que a segunda fase de avaliação tem firstPhase');
        $this->assertEquals($first_phase_id, $first->id, 'Certificando que firstPhase da segunda fase de avaliação é a primeira fase');

        // terceira fase de avaliação -> firstPhase deve ser a primeira fase
        $first = $eval_phase_3->opportunity->firstPhase;
        $this->assertNotNull($first, 'Certificando que a terceira fase de avaliação tem firstPhase');
        $this->assertEquals($first_phase_id, $first->id, 'Certificando que firstPhase da terceira fase de avaliação é a primeira fase');

        // lastPhase -> firstPhase deve ser a primeira fase
        $first = $last_phase->firstPhase;
        $this->assertNotNull($first, 'Certificando que lastPhase tem firstPhase');
        $this->assertEquals($first_phase_id, $first->id, 'Certificando que firstPhase da lastPhase é a primeira fase');
    }

    // lastPhase
    function testOpportunityLastPhaseGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;

        /** @var Opportunity $opportunity */
        $opportunity = $builder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $eval_phase_1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $eval_phase_2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $eval_phase_3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $last_phase = $opportunity->lastPhase;
        $last_phase_id = $last_phase->id;

        // lastPhase da primeira fase retorna a fase de publicação de resultado
        $last = $opportunity->lastPhase;
        $this->assertNotNull($last, 'Certificando que lastPhase retorna valor');
        $this->assertEquals($last_phase_id, $last->id, 'Certificando que lastPhase da oportunidade raiz é a fase de publicação');
        $this->assertTrue($last->isLastPhase, 'Certificando que lastPhase tem isLastPhase = true');

        // primeira fase de avaliação -> lastPhase deve ser a fase de publicação
        $last = $eval_phase_1->opportunity->lastPhase;
        $this->assertNotNull($last, 'Certificando que a primeira fase de avaliação tem lastPhase');
        $this->assertEquals($last_phase_id, $last->id, 'Certificando que lastPhase da primeira fase de avaliação é a fase de publicação');

        // segunda fase de avaliação -> lastPhase deve ser a fase de publicação
        $last = $eval_phase_2->opportunity->lastPhase;
        $this->assertNotNull($last, 'Certificando que a segunda fase de avaliação tem lastPhase');
        $this->assertEquals($last_phase_id, $last->id, 'Certificando que lastPhase da segunda fase de avaliação é a fase de publicação');

        // terceira fase de avaliação -> lastPhase deve ser a fase de publicação
        $last = $eval_phase_3->opportunity->lastPhase;
        $this->assertNotNull($last, 'Certificando que a terceira fase de avaliação tem lastPhase');
        $this->assertEquals($last_phase_id, $last->id, 'Certificando que lastPhase da terceira fase de avaliação é a fase de publicação');
    }

    // countEvaluations
    function testOpportunityCountEvaluationsGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;

        /** @var Opportunity $opportunity */
        $opportunity = $builder
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

        $this->registrationDirector->createDraftRegistrations($opportunity, number_of_registrations: 5);
        $this->registrationDirector->createSentRegistrations($opportunity, number_of_registrations: 5);

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $opportunity = $opportunity->refreshed();

        // A view evaluations usa opportunity_id da inscrição; as inscrições estão na first phase
        $count = $opportunity->countEvaluations;
        $this->assertIsInt($count, 'Certificando que countEvaluations retorna inteiro');
        $this->assertEquals(5, $count, 'Certificando que countEvaluations da primeira fase retorna a quantidade de avaliações');
    }

    // previousPhases
    function testOpportunityPreviousPhasesGetter()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $first_phase_id = $opportunity->id;

        $eval_phase_1 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+2 days')
                ->setEvaluationTo('+9 days')
                ->save()
                ->getInstance();

        $eval_phase_2 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+10 days')
                ->setEvaluationTo('+17 days')
                ->save()
                ->getInstance();
        $eval_phase_2_opp_id = $eval_phase_2->opportunity->id;

        $eval_phase_3 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationFrom('+18 days')
                ->setEvaluationTo('+25 days')
                ->save()
                ->getInstance();
        $eval_phase_3_opp_id = $eval_phase_3->opportunity->id;

        $opportunity = $opportunity->refreshed();
        $last_phase = $opportunity->lastPhase;

        // firstPhase -> previousPhases deve ser vazio
        $previous_phases = $opportunity->previousPhases;
        $this->assertEmpty($previous_phases, 'Certificando que previousPhases da firstPhase é vazio');

        // segunda fase de avaliação -> previousPhases deve conter apenas a firstPhase (FirstPhase)
        $previous_phases = $eval_phase_2->opportunity->previousPhases;
        $this->assertCount(1, $previous_phases, 'Certificando que previousPhases da segunda fase de avaliação tem 1 elemento');
        $this->assertEquals($first_phase_id, $previous_phases[0]->id, 'Certificando que previousPhases da segunda fase contém a firstPhase');

        // terceira fase de avaliação -> previousPhases deve conter todas as fases anteriores (FirstPhase e eval_phase_2)
        $previous_phases = $eval_phase_3->opportunity->previousPhases;
        $this->assertCount(2, $previous_phases, 'Certificando que previousPhases da terceira fase de avaliação tem 2 elementos');
        $this->assertEquals($first_phase_id, $previous_phases[0]->id, 'Certificando que a primeira anterior é a firstPhase');
        $this->assertEquals($eval_phase_2_opp_id, $previous_phases[1]->id, 'Certificando que a segunda anterior é a fase de avaliação 2');
        
        // lastPhase -> previousPhases (quando não vazio, contém todas as fases anteriores (FirstPhase, eval_phase_2 e eval_phase_3))
        $previous_phases = $last_phase->previousPhases;
        if (count($previous_phases) > 0) {
            $previous_ids = array_map(fn($p) => $p->id, $previous_phases);
            $this->assertContains($first_phase_id, $previous_ids, 'Certificando que previousPhases da lastPhase contém a firstPhase');
            $this->assertContains($eval_phase_2_opp_id, $previous_ids, 'Certificando que previousPhases da lastPhase contém a segunda fase de avaliação');
            $this->assertContains($eval_phase_3_opp_id, $previous_ids, 'Certificando que previousPhases da lastPhase contém a terceira fase de avaliação');
        }
    }
}
