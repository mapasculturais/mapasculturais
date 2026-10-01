<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão: a consolidação do resultado de uma inscrição avaliada por método
 * técnico deve gravar score e eligible na inscrição da fase atual e propagar
 * para as inscrições de TODAS as fases seguintes
 * (hook entity(Registration).consolidateResult do módulo EvaluationMethodTechnical).
 */
class TechnicalEvaluationScorePhasePropagationTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function fetchRegistrationRow(int $id): array
    {
        $conn = $this->app->em->getConnection();

        return $conn->fetchAssociative(
            'SELECT id, score, eligible FROM registration WHERE id = :id',
            ['id' => $id]
        );
    }

    function testConsolidateResultPropagatesScoreAndEligibleToAllNextPhases()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /*
         * Estrutura de fases:
         * - fase 1 (principal): coleta de inscrições, com pergunta de cotas e campo "raca"
         * - fase 2: avaliação técnica (comissão com 2 avaliadores, critério de 0 a 10 e cota "Pessoas Negras")
         * - fase 3 e fase 4: fases de avaliação simples seguintes
         */
        $technical_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->createStep('Informações')
                ->createOwnerField(identifier: 'raca', entity_field: 'raca', title: 'Raça/Cor', required: true)
                ->save()
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::documentary)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setAutoApplicationAllowed(true)
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->config()
                    ->addSection('sec1', 'Seção 1')
                    ->addCriterion('cri1', 'sec1', 'Critério 1', 0, 10, 1)
                    ->quota()
                        ->addRule('Pessoas Negras', 2)
                        ->addRuleField('raca', ['Preta', 'Parda'])
                        ->done()
                    ->done()
                ->save()
                ->addValuers(2, 'Comissão');

        /** @var Opportunity $technical_phase */
        $technical_phase = $technical_phase_builder->getInstance()->opportunity;

        /** @var Opportunity $phase3 */
        $phase3 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance()
                ->opportunity;

        /** @var Opportunity $phase4 */
        $phase4 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance()
                ->opportunity;

        /** @var Opportunity $first_phase */
        $first_phase = $this->opportunityBuilder
            ->save()
            ->refresh()
            ->getInstance();

        $field_raca = $this->opportunityBuilder->getFieldName('raca', $first_phase);

        // Inscrição na primeira fase, candidata à cota "Pessoas Negras"
        /** @var Registration $registration_first */
        $registration_first = $this->registrationDirector->createSentRegistration($first_phase, [
            $field_raca => 'Preta',
            'appliedForQuota' => true,
        ]);
        $number = $registration_first->number;

        $registration_first->setStatusToApproved(true);
        $registration_first = $registration_first->refreshed();

        $repo = $this->app->repo('Registration');

        // Propaga a inscrição aprovada para a fase de avaliação técnica
        $technical_phase->syncRegistrations([$registration_first]);
        $this->processJobs();

        /** @var Registration $registration_technical */
        $registration_technical = $repo->findOneBy(['number' => $number, 'opportunity' => $technical_phase]);
        $this->assertNotNull($registration_technical, 'Inscrição deve ter sido propagada para a fase técnica');

        // Copia os campos da primeira fase para a inscrição da fase técnica
        // (mesmo padrão de EvaluationMethodTechnicalTest::prepareTechnicalScenarioRegistrations)
        $registration_first = $registration_first->refreshed();
        $registration_technical = $registration_technical->refreshed();

        foreach ($first_phase->registrationFieldConfigurations as $field) {
            $field_name = $field->fieldName;
            if (isset($registration_first->$field_name)) {
                $registration_technical->$field_name = $registration_first->$field_name;
            }
        }

        $registration_technical->save(true);

        /*
         * Distribui a comissão e envia a avaliação do primeiro avaliador (8.0)
         * com a inscrição ainda pendente (a distribuição só considera inscrições
         * com status <= 1). O resultado final ainda não está consolidado.
         */
        $technical_phase = $technical_phase->refreshed();
        $technical_phase->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration_technical = $registration_technical->refreshed();

        $technical_phase_builder->withValuer('Comissão', 'Comissão - valuer 1')
            ->evaluation($registration_technical)
                ->setCriterionScore('cri1', 8.0)
                ->save()
                ->send()
                ->done();

        /*
         * Aplica o resultado e propaga a inscrição para as duas fases seguintes,
         * criando a cadeia nextPhaseRegistrationId: fase técnica -> fase 3 -> fase 4
         */
        $registration_technical->setStatusToApproved(true);
        $registration_technical = $registration_technical->refreshed();

        $phase3->syncRegistrations([$registration_technical]);
        $this->processJobs();

        /** @var Registration $registration_phase3 */
        $registration_phase3 = $repo->findOneBy(['number' => $number, 'opportunity' => $phase3]);
        $this->assertNotNull($registration_phase3, 'Inscrição deve ter sido propagada para a fase 3');

        $registration_phase3->setStatusToApproved(true);
        $registration_phase3 = $registration_phase3->refreshed();

        $phase4->syncRegistrations([$registration_phase3]);
        $this->processJobs();

        /** @var Registration $registration_phase4 */
        $registration_phase4 = $repo->findOneBy(['number' => $number, 'opportunity' => $phase4]);
        $this->assertNotNull($registration_phase4, 'Inscrição deve ter sido propagada para a fase 4');

        // Sanidade: os ponteiros de próxima fase estão encadeados
        $this->assertEquals(
            $registration_phase3->id,
            $registration_technical->refreshed()->nextPhase->id,
            'nextPhase da inscrição da fase técnica deve ser a inscrição da fase 3'
        );
        $this->assertEquals(
            $registration_phase4->id,
            $registration_phase3->refreshed()->nextPhase->id,
            'nextPhase da inscrição da fase 3 deve ser a inscrição da fase 4'
        );

        // As inscrições das fases seguintes ainda não têm o score final (7.0):
        // ele só existe após o envio da avaliação do segundo avaliador
        foreach (['fase 3' => $registration_phase3, 'fase 4' => $registration_phase4] as $label => $registration) {
            $row = $this->fetchRegistrationRow($registration->id);
            $this->assertNotEquals(
                7.0,
                (float) $row['score'],
                "Score da inscrição da {$label} não deve ser 7.0 antes da consolidação final da avaliação técnica"
            );
        }

        /*
         * O segundo avaliador envia a avaliação (6.0) DEPOIS de a inscrição já ter
         * sido propagada para as fases seguintes: o envio dispara o
         * consolidateResult, que consolida a média (8.0 + 6.0) / 2 = 7.0 e deve
         * gravar score e eligible na inscrição da fase técnica e propagar para
         * as inscrições de TODAS as fases seguintes.
         */
        $registration_technical = $registration_technical->refreshed();

        $technical_phase_builder->withValuer('Comissão', 'Comissão - valuer 2')
            ->evaluation($registration_technical)
                ->setCriterionScore('cri1', 6.0)
                ->save()
                ->send()
                ->done();

        $registrations_by_phase = [
            'fase técnica' => $registration_technical,
            'fase 3' => $registration_phase3,
            'fase 4' => $registration_phase4,
        ];

        foreach ($registrations_by_phase as $phase_label => $registration) {
            $row = $this->fetchRegistrationRow($registration->id);

            $this->assertEqualsWithDelta(
                7.0,
                (float) $row['score'],
                0.001,
                "Score da inscrição da {$phase_label} deve ser a média das avaliações técnicas (7.0)"
            );

            $this->assertTrue(
                filter_var($row['eligible'], FILTER_VALIDATE_BOOL),
                "Eligible da inscrição da {$phase_label} deve ser true após a consolidação (candidata à cota Pessoas Negras)"
            );
        }
    }
}
