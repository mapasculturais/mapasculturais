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
}
