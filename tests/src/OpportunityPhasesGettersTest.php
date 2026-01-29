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
}
