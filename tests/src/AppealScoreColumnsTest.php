<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use OpportunityAppealPhase\AppealReview\CorrectorEnvironment;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * F4 (#20) — CA-11: colunas de nota original/corrigida/diferença.
 *
 * Cenário documental com correção APLICADA (slot A: inválida −1 → válida 1;
 * slot B sem correção: válida 1; inscrição 2 sem nenhuma correção):
 * - por slot: originalScore/correctedScore/scoreDifference via ApiQuery e API;
 * - por inscrição: averageOriginalScore/averageCorrectedScore/scoreDifference
 *   (média corrigida = vigente; média original = recomputada com os snapshots).
 */
class AppealScoreColumnsTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector,
        RequestFactory;

    private function enableFlag(): void
    {
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';
    }

    /**
     * @return array{admin: User, committeeUser: User, opportunity: Opportunity,
     *   registration1: Registration, registration2: Registration,
     *   slotA: RegistrationEvaluation, slotB: RegistrationEvaluation}
     */
    private function createScenario(): array
    {
        $this->enableFlag();

        $admin = $this->userDirector->createUser('admin');
        $slot_owner_a = $this->userDirector->createUser();
        $slot_owner_b = $this->userDirector->createUser();
        $committee_user = $this->userDirector->createUser();

        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::documentary)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('Comissão', 'Avaliador A', $slot_owner_a->profile)
                    ->done()
                ->addValuer('Comissão', 'Avaliador B', $slot_owner_b->profile)
                    ->done()
                ->done()
            ->save();

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder->getInstance();

        $registration_1 = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_2 = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        // Slot A: inválida (result −1) — será corrigida para válida (result 1).
        $slot_a = new RegistrationEvaluation();
        $slot_a->registration = $registration_1;
        $slot_a->user = $slot_owner_a;
        $slot_a->status = RegistrationEvaluation::STATUS_SENT;
        $slot_a->setEvaluationData((object) ['doc_a' => ['evaluation' => 'invalid', 'obs' => '']]);
        $slot_a->save(true);

        // Slot B: válida (result 1) — permanece sem correção.
        $slot_b = new RegistrationEvaluation();
        $slot_b->registration = $registration_1;
        $slot_b->user = $slot_owner_b;
        $slot_b->status = RegistrationEvaluation::STATUS_SENT;
        $slot_b->setEvaluationData((object) ['doc_b' => ['evaluation' => 'valid', 'obs' => '']]);
        $slot_b->save(true);

        // Inscrição 2: um slot válido, sem nenhuma correção.
        $slot_c = new RegistrationEvaluation();
        $slot_c->registration = $registration_2;
        $slot_c->user = $slot_owner_b;
        $slot_c->status = RegistrationEvaluation::STATUS_SENT;
        $slot_c->setEvaluationData((object) ['doc_b' => ['evaluation' => 'valid', 'obs' => '']]);
        $slot_c->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso documental';
        $appeal_phase->ownerEntity = $opportunity->ownerEntity;
        $appeal_phase->owner = $opportunity->owner;
        $appeal_phase->registrationCategories = $opportunity->registrationCategories;
        $appeal_phase->registrationRanges = $opportunity->registrationRanges;
        $appeal_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;
        $appeal_phase->isDataCollection = true;
        $appeal_phase->isAppealPhase = true;
        $appeal_phase->registrationFrom = new DateTime('-1 day');
        $appeal_phase->registrationTo = new DateTime('+1 day');
        $appeal_phase->save(true);

        $opportunity->appealPhase = $appeal_phase;
        $opportunity->save(true);

        $appeal_emc = new EvaluationMethodConfiguration();
        $appeal_emc->opportunity = $appeal_phase;
        $appeal_emc->type = 'continuous';
        $appeal_emc->publishEvaluationDetails = true;
        $appeal_emc->save(true);

        $appeal_phase->evaluationMethodConfiguration = $appeal_emc;
        $appeal_phase->save(true);

        $appeal_emc->createAgentRelation($committee_user->profile, 'committee 1', true)->save(true);

        $app->enableAccessControl();

        return [
            'admin' => $admin,
            'committeeUser' => $committee_user,
            'opportunity' => $opportunity,
            'registration1' => $registration_1,
            'registration2' => $registration_2,
            'slotA' => $slot_a,
            'slotB' => $slot_b,
            'appealPhase' => $appeal_phase,
        ];
    }

    /**
     * @return array{status: int, json: mixed}
     */
    private function dispatch(ServerRequestInterface $request): array
    {
        $app = App::i();
        $app->reset();
        $app->run($request, false);

        return [
            'status' => $app->response->getStatusCode(),
            'json' => json_decode((string) $app->response->getBody(), true),
        ];
    }

    /**
     * Designa o comitê para o slot e aplica a correção (inválida → válida).
     */
    private function applyRealCorrection(array $scenario): void
    {
        $this->login($scenario['admin']);

        $created = $this->dispatch(
            $this->requestFactory->POST('registrationappealreview', 'index', [], [
                'originalEvaluation' => $scenario['slotA']->id,
                'correctorUser' => $scenario['committeeUser']->id,
            ])
        );
        $this->assertEquals(200, $created['status'], 'Designação: ' . json_encode($created['json']));

        $this->login($scenario['committeeUser']);

        $sent = $this->dispatch(
            $this->requestFactory->POST('registrationevaluation', 'applyAppealCorrection', [$scenario['slotA']->id], [
                'evaluationData' => ['doc_a' => ['evaluation' => 'valid', 'obs' => 'corrigida']],
                'draft' => false,
            ])
        );
        $this->assertEquals(200, $sent['status'], 'Correção aplicada: ' . json_encode($sent['json']));

        $this->login($scenario['admin']);
        // Sem processPCache: o drain da fila com fase de recurso no cenário
        // esbarra em query pré-existente do OpportunityPhases (EAGER+WITH em
        // OpportunityMeta#owner) — fora do escopo do F4. As consultas abaixo
        // rodam como admin (bypass da subquery de permissão).
    }

    function testSlotColumnsWithAndWithoutCorrection(): void
    {
        $scenario = $this->createScenario();
        $this->applyRealCorrection($scenario);

        $query = new ApiQuery(RegistrationEvaluation::class, [
            '@select' => 'id,originalScore,correctedScore,scoreDifference',
            'id' => "IN({$scenario['slotA']->id},{$scenario['slotB']->id})",
        ]);
        $rows = [];
        foreach ($query->find() as $row) {
            $rows[(int) $row['id']] = $row;
        }

        // Slot corrigido: −1 → 1 (documental: inválida → válida)
        $this->assertEquals(-1.0, $rows[$scenario['slotA']->id]['originalScore'], 'originalScore = snapshot da primeira correção aplicada');
        $this->assertEquals(1.0, $rows[$scenario['slotA']->id]['correctedScore'], 'correctedScore = nota vigente (result pós correção in-place)');
        $this->assertEquals(2.0, $rows[$scenario['slotA']->id]['scoreDifference'], 'scoreDifference = corrigida − original');

        // Slot sem correção: original = vigente, diferença 0
        $this->assertEquals(1.0, $rows[$scenario['slotB']->id]['originalScore'], 'sem correção: originalScore = nota atual');
        $this->assertEquals(1.0, $rows[$scenario['slotB']->id]['correctedScore']);
        $this->assertEquals(0.0, $rows[$scenario['slotB']->id]['scoreDifference']);
    }

    /**
     * Reabertura pelo gestor: a correção aplicada segue vigente e a nota
     * original continua vindo do snapshot (colunas e ambiente do corretor).
     */
    function testReopenedCorrectionKeepsOriginalScoreVisible(): void
    {
        $scenario = $this->createScenario();
        $this->applyRealCorrection($scenario);

        $app = App::i();
        /** @var RegistrationAppealReview $review */
        $review = $app->repo(RegistrationAppealReview::class)->findOneBy(['originalEvaluation' => $scenario['slotA']->id]);

        $reopened = $this->dispatch(
            $this->requestFactory->POST('registrationappealreview', 'reopen', [$review->id], [])
        );
        $this->assertEquals(200, $reopened['status'], 'Reabertura: ' . json_encode($reopened['json']));

        $query = new ApiQuery(RegistrationEvaluation::class, [
            '@select' => 'id,originalScore,correctedScore,scoreDifference',
            'id' => "EQ({$scenario['slotA']->id})",
        ]);
        $row = $query->find()[0];

        $this->assertEquals(-1.0, $row['originalScore'], 'Reaberta: original continua sendo o snapshot, não a nota vigente.');
        $this->assertEquals(1.0, $row['correctedScore']);
        $this->assertEquals(2.0, $row['scoreDifference'], 'Reaberta: diferença não zera enquanto o corretor revisa.');

        $app->em->refresh($review);
        $this->login($scenario['committeeUser']);
        $environment = (new CorrectorEnvironment())->build($review);
        $original = (array) $environment['originalEvaluation'];

        $this->assertEquals('invalid', ((array) $original['doc_a'])['evaluation'], 'Ambiente do corretor mostra a avaliação original, não a já corrigida.');
        $this->assertEquals('valid', ((array) ((array) $environment['draft'])['doc_a'])['evaluation'], 'Rascunho pré-preenchido com a correção enviada.');
    }

    /**
     * Lista da fase de recurso: a linha é a inscrição do recurso, mas as
     * médias vêm da inscrição de mesmo número na fase principal (onde a
     * correção foi aplicada), não da avaliação do recurso.
     */
    function testAppealPhaseRowUsesMainPhaseScores(): void
    {
        $scenario = $this->createScenario();
        $this->applyRealCorrection($scenario);

        $app = App::i();
        $main_registration = $scenario['registration1'];

        $app->disableAccessControl();
        $appeal_registration = new Registration();
        $appeal_registration->opportunity = $scenario['appealPhase'];
        $appeal_registration->owner = $main_registration->owner;
        $appeal_registration->number = $main_registration->number;
        $appeal_registration->status = Registration::STATUS_APPROVED;
        $appeal_registration->save(true);

        // Avaliação do recurso (contínua): resultado 10 = código de status "deferido".
        $appeal_evaluation = new RegistrationEvaluation();
        $appeal_evaluation->registration = $appeal_registration;
        $appeal_evaluation->user = $scenario['committeeUser'];
        $appeal_evaluation->status = RegistrationEvaluation::STATUS_SENT;
        $appeal_evaluation->setEvaluationData((object) ['status' => '10', 'obs' => 'deferido']);
        $appeal_evaluation->save(true);
        $app->enableAccessControl();

        $this->assertEquals('10', (string) $appeal_evaluation->result, 'Pré-condição: avaliação do recurso com resultado 10.');

        $query = new ApiQuery(Registration::class, [
            '@select' => 'id,averageOriginalScore,averageCorrectedScore,scoreDifference',
            'id' => "IN({$appeal_registration->id},{$main_registration->id})",
        ]);
        $rows = [];
        foreach ($query->find() as $row) {
            $rows[(int) $row['id']] = $row;
        }

        $appeal_row = $rows[$appeal_registration->id];
        $this->assertEquals(0.0, $appeal_row['averageOriginalScore'], 'Recurso: média original da fase principal, não o 10 da avaliação do recurso.');
        $this->assertEquals(1.0, $appeal_row['averageCorrectedScore'], 'Recurso: média corrigida da fase principal.');
        $this->assertEquals(1.0, $appeal_row['scoreDifference']);

        $main_row = $rows[$main_registration->id];
        $this->assertEquals($main_row['averageOriginalScore'], $appeal_row['averageOriginalScore'], 'Mesma média nas duas listas.');
        $this->assertEquals($main_row['averageCorrectedScore'], $appeal_row['averageCorrectedScore']);
    }

    function testRegistrationAveragesAndDifference(): void
    {
        $scenario = $this->createScenario();
        $this->applyRealCorrection($scenario);

        $query = new ApiQuery(Registration::class, [
            '@select' => 'id,averageOriginalScore,averageCorrectedScore,scoreDifference',
            'id' => "IN({$scenario['registration1']->id},{$scenario['registration2']->id})",
        ]);
        $rows = [];
        foreach ($query->find() as $row) {
            $rows[(int) $row['id']] = $row;
        }

        // Inscrição 1: slots (−1→1) e (1) → média original 0, corrigida 1, diferença 1
        $reg1 = $rows[$scenario['registration1']->id];
        $this->assertEquals(0.0, $reg1['averageOriginalScore'], 'média original = (−1 + 1) / 2');
        $this->assertEquals(1.0, $reg1['averageCorrectedScore'], 'média corrigida = (1 + 1) / 2');
        $this->assertEquals(1.0, $reg1['scoreDifference']);

        // Inscrição 2: sem nenhuma correção → médias iguais, diferença 0
        $reg2 = $rows[$scenario['registration2']->id];
        $this->assertEquals(1.0, $reg2['averageOriginalScore']);
        $this->assertEquals(1.0, $reg2['averageCorrectedScore']);
        $this->assertEquals(0.0, $reg2['scoreDifference'], 'sem correção: diferença 0');
    }

    /**
     * Query API real (proof): @select das propriedades computadas retorna
     * valores no cenário com correção (documental −1 → 1).
     */
    function testApiSelectReturnsComputedScoreColumns(): void
    {
        $scenario = $this->createScenario();
        $this->applyRealCorrection($scenario);

        $evaluation_response = $this->dispatch(
            $this->requestFactory->GET('api/registrationevaluation', 'find', [], [
                '@select' => 'id,originalScore,correctedScore,scoreDifference',
                'id' => "EQ({$scenario['slotA']->id})",
            ], ajax: true)
        );

        $this->assertEquals(200, $evaluation_response['status'], 'API find de avaliações: ' . json_encode($evaluation_response['json']));
        $slot = $evaluation_response['json'][0] ?? null;
        $this->assertNotNull($slot);
        $this->assertEquals(-1.0, $slot['originalScore'], 'API: originalScore do slot corrigido');
        $this->assertEquals(1.0, $slot['correctedScore'], 'API: correctedScore do slot corrigido');
        $this->assertEquals(2.0, $slot['scoreDifference'], 'API: scoreDifference do slot corrigido');

        $registration_response = $this->dispatch(
            $this->requestFactory->GET('api/registration', 'find', [], [
                '@select' => 'id,averageOriginalScore,averageCorrectedScore,scoreDifference',
                'id' => "EQ({$scenario['registration1']->id})",
            ], ajax: true)
        );

        $this->assertEquals(200, $registration_response['status'], 'API find de inscrições: ' . json_encode($registration_response['json']));
        $registration = $registration_response['json'][0] ?? null;
        $this->assertNotNull($registration);
        $this->assertEquals(0.0, $registration['averageOriginalScore'], 'API: média original da inscrição');
        $this->assertEquals(1.0, $registration['averageCorrectedScore'], 'API: média corrigida da inscrição');
        $this->assertEquals(1.0, $registration['scoreDifference'], 'API: diferença da inscrição');
    }
}
