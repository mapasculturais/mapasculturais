<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
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

class OpportunityPhasesTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;


    function testFirstEvaluationPhaseDeletion() {
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
        

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();

        $phases = $opportunity->phases;

        $this->assertEquals($opportunity->id, $phases[1]->opportunity->id, "Garantindo que uma segunda fase de avaliação, após a exclusão da primeira fase de avaliação, esteja vinculada a primeira fase de coletada de dados");
    
    }

    function testSecondEvaluationPhaseDeletion() {
        $app = $this->app;

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;
        
        /** @var Opportunity */
        $opportunity = $builder->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->getInstance();

        $eval_phase1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();
        


        $this->assertFalse((bool) $eval_phase2->opportunity->isDataCollection, 'Garantindo que a  Opportunity vinculada a uma fase de avaliação que segue outra fase de avaliação não é uma coleta de dados');

        $hidden_opportunity_id = $eval_phase2->opportunity->id;
        
        $eval_phase2->delete(flush: true);

        $hidden_opportunity = $app->repo('Opportunity')->find($hidden_opportunity_id);

        $this->assertNull($hidden_opportunity, 'Garantindo que a exclusão de uma fase de avaliação exclua a Opportunity vinculada quando essa não é uma coleta de dados');
    
    }

    function testFieldsVisibleForEvaluatorsPersistedPerPhase()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setCategories(['Categoria A'])
            ->setProponentTypes(['Pessoa Física'])
            ->setRanges([
                ['label' => 'Faixa 1', 'limit' => 10, 'value' => 1]
            ])
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa principal')
                    ->createField('campo-a', 'text', title: 'Campo A')
                    ->createField('campo-b', 'text', title: 'Campo B')
                    ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('com-1', 1)
                ->save()
                ->addValuers(1, 'com-1')
                ->done()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new After)
                ->setCommitteeValuersPerRegistration('com-2', 1)
                ->save()
                ->addValuers(1, 'com-2')
                ->done()
            ->getInstance();

        $evaluation_phases = [];
        $phase = $opportunity->firstPhase;
        while ($phase) {
            if ($phase->evaluationMethodConfiguration) {
                $evaluation_phases[] = $phase;
            }
            $phase = $phase->nextPhase;
        }

        $this->assertCount(2, $evaluation_phases, 'Garantindo que a oportunidade possui 2 fases de avaliações');

        usort($evaluation_phases, function (Opportunity $a, Opportunity $b) {
            return $a->evaluationMethodConfiguration->evaluationFrom <=> $b->evaluationMethodConfiguration->evaluationFrom;
        });

        /** @var Opportunity $phase_1 */
        $phase_1 = $evaluation_phases[0];
        /** @var Opportunity $phase_2 */
        $phase_2 = $evaluation_phases[1];

        $field_a = $this->opportunityBuilder->getFieldName('campo-a');
        $field_b = $this->opportunityBuilder->getFieldName('campo-b');

        $phase_1->setAvaliableEvaluationFields([$field_a => 'true']);
        $phase_1->save(true);

        $phase_2->setAvaliableEvaluationFields([$field_b => 'true']);
        $phase_2->save(true);

        $registration_base = $this->registrationDirector->createSentRegistration(
            $opportunity->firstPhase,
            data: [
                $field_a => 'VALOR_A_BASE',
                $field_b => 'VALOR_B_BASE'
            ]
        );

        // Garante que as definições de metadados existem mesmo para fases futuras.
        $phase_1->registerRegistrationMetadata(true);
        $phase_2->registerRegistrationMetadata(true);

        $app = App::i();
        $phasesModule = $app->modules['OpportunityPhases'];

        // Cria os registros das fases de avaliações
        $registration_1 = $phasesModule->createPhaseRegistration($phase_1, $registration_base);
        $registration_1->$field_a = 'VALOR_A_P1';
        $registration_1->$field_b = 'VALOR_B_P1';
        $registration_1->save(true);
        $registration_1->send(false);
        $registration_1 = $registration_1->refreshed();

        $registration_2 = $phasesModule->createPhaseRegistration($phase_2, $registration_base);
        $registration_2->$field_a = 'VALOR_A_P2';
        $registration_2->$field_b = 'VALOR_B_P2';
        $registration_2->save(true);
        $registration_2->send(false);
        $registration_2 = $registration_2->refreshed();

        // Redistribui avaliadores para que viewUserEvaluation fique habilitado.
        $phase_1->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $phase_2->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $registration_1 = $registration_1->refreshed();
        $registration_2 = $registration_2->refreshed();

        $valuer_user_id_1 = array_key_first($registration_1->valuers);
        $valuer_user_id_2 = array_key_first($registration_2->valuers);

        $valuer_user_1 = $this->app->repo('User')->find((int) $valuer_user_id_1);
        $valuer_user_2 = $this->app->repo('User')->find((int) $valuer_user_id_2);

        $this->assertNotNull($valuer_user_1, 'Avaliador da fase 1 deve existir');
        $this->assertNotNull($valuer_user_2, 'Avaliador da fase 2 deve existir');

        // Phase 1: avaliador deve enxergar somente campo-a.
        $this->login($valuer_user_1);
        $json_1 = $registration_1->jsonSerialize();
        $this->assertArrayHasKey($field_a, $json_1, 'Campo habilitado deve aparecer no jsonSerialize do avaliador da fase 1');
        $this->assertFalse(isset($json_1[$field_b]), 'Campo desabilitado deve não aparecer no jsonSerialize do avaliador da fase 1');

        // Phase 2: avaliador deve enxergar somente campo-b.
        $this->login($valuer_user_2);
        $json_2 = $registration_2->jsonSerialize();
        $this->assertArrayHasKey($field_b, $json_2, 'Campo habilitado deve aparecer no jsonSerialize do avaliador da fase 2');
        $this->assertFalse(isset($json_2[$field_a]), 'Campo desabilitado deve não aparecer no jsonSerialize do avaliador da fase 2');
    }
}
