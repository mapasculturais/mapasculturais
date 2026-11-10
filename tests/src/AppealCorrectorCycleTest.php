<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\User;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RegistrationFieldBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * F7 (#51): ciclo do corretor para métodos não-técnicos (documental/simples).
 *
 * Cobertura (por método):
 * - designação (F6 aberta) → corretor designado ACESSA o slot (canUser via
 *   hook do módulo + ambiente GET 200, antes PermissionDenied) → rascunho →
 *   envio → RegistrationEvaluation atualizada in-place + snapshot
 *   originalValue + designação SENT;
 * - escopo restrito documental: campo fora do escopo com valor divergente é
 *   rejeitado (400); campo dentro do escopo é aceito.
 */
class AppealCorrectorCycleTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RegistrationFieldBuilder,
        UserDirector,
        RequestFactory;

    private function enableFlag(): void
    {
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';
    }

    /**
     * @return array{admin: User, slotOwner: User, committeeUser: User,
     *   opportunity: Opportunity, registration: Registration,
     *   slot: RegistrationEvaluation, appealPhase: Opportunity,
     *   fields: array<string, RegistrationFieldConfiguration>}
     */
    private function createScenario(EvaluationMethods $method): array
    {
        $this->enableFlag();

        $admin = $this->userDirector->createUser('admin');
        $slot_owner = $this->userDirector->createUser();
        $committee_user = $this->userDirector->createUser();

        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase($method)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('Comissão', 'Avaliador do Slot', $slot_owner->profile)
                    ->done()
                ->done()
            ->save();

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder->getInstance();

        $fields = [];
        if ($method === EvaluationMethods::documentary) {
            // Campos exigem uma etapa (isFieldVisisble → isStepVisible), no
            // mesmo padrão da criação de fase de recurso do módulo.
            $app = App::i();
            $app->disableAccessControl();

            $step = new RegistrationStep();
            $step->opportunity = $opportunity;
            $step->name = '';
            $step->save(true);

            $app->enableAccessControl();

            foreach (['A' => 'Documento A', 'B' => 'Documento B'] as $key => $title) {
                $field = $this->registrationFieldBuilder
                    ->reset($opportunity, 'text', 'doc-field-' . strtolower($key))
                    ->fillRequiredProperties()
                    ->setTitle($title)
                    ->setStep($step)
                    ->save()
                    ->getInstance();

                $fields[$title] = $field;
            }

            $opportunity->registerRegistrationMetadata();
        }

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $slot = new RegistrationEvaluation();
        $slot->registration = $registration;
        $slot->user = $slot_owner;
        $slot->status = RegistrationEvaluation::STATUS_EVALUATED;

        if ($method === EvaluationMethods::documentary) {
            $data = [];
            foreach ($fields as $field) {
                $data[$field->getFieldName()] = ['evaluation' => 'valid', 'obs' => ''];
            }
            $slot->setEvaluationData((object) $data);
        } else {
            $slot->setEvaluationData((object) ['status' => '3', 'obs' => 'original']);
        }

        $slot->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso ' . $method->name;
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
            'slotOwner' => $slot_owner,
            'committeeUser' => $committee_user,
            'opportunity' => $opportunity,
            'registration' => $registration,
            'slot' => $slot,
            'appealPhase' => $appeal_phase,
            'fields' => $fields,
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
     * @return array{status: int, json: mixed}
     */
    private function designate(array $scenario, ?array $released_scope = null): array
    {
        $this->login($scenario['admin']);

        $payload = [
            'originalEvaluation' => $scenario['slot']->id,
            'correctorUser' => $scenario['committeeUser']->id,
        ];

        if ($released_scope !== null) {
            $payload['releasedScope'] = json_encode($released_scope);
        }

        return $this->dispatch(
            $this->requestFactory->POST('registrationappealreview', 'index', [], $payload)
        );
    }

    /**
     * @return array{status: int, json: mixed}
     */
    private function environment(int $assignment_id, User $corrector): array
    {
        $this->login($corrector);

        return $this->dispatch(
            $this->requestFactory->GET('appealCorrector', 'environment', [$assignment_id], ajax: true)
        );
    }

    /**
     * @return array{status: int, json: mixed}
     */
    private function applyCorrection(RegistrationEvaluation $slot, array $evaluation_data, bool $draft): array
    {
        return $this->dispatch(
            $this->requestFactory->POST('registrationevaluation', 'applyAppealCorrection', [$slot->id], [
                'evaluationData' => $evaluation_data,
                'draft' => $draft,
            ])
        );
    }

    /**
     * Documental: designar → corretor acessa (hook + ambiente por método) →
     * rascunho → envio → slot atualizado in-place + snapshot + SENT.
     */
    function testDocumentaryCorrectorFullCycle(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::documentary);
        [$field_a, $field_b] = array_values($scenario['fields']);
        $field_a_name = $field_a->getFieldName();
        $field_b_name = $field_b->getFieldName();

        $created = $this->designate($scenario);
        $this->assertEquals(200, $created['status'], 'Designação em documental (F6): ' . json_encode($created['json']));
        $review_id = (int) $created['json']['id'];

        // Antes do F7: PermissionDenied. Agora: hook concede view/modify ao corretor.
        $corrector = $scenario['committeeUser'];
        $this->login($corrector);
        $this->assertTrue($scenario['slot']->canUser('view', $corrector), 'Corretor designado deve ver o slot documental (hook do módulo, F7).');
        $this->assertTrue($scenario['slot']->canUser('modify', $corrector), 'Corretor designado deve modificar o slot documental (hook do módulo, F7).');

        // Ambiente por método: method=documentary + campos liberados (sem restrição)
        $environment = $this->environment($review_id, $corrector);
        $this->assertEquals(200, $environment['status'], 'Ambiente do corretor em documental: ' . json_encode($environment['json']));
        $this->assertSame('documentary', $environment['json']['method']);
        $field_ids = array_map(static fn (array $f): string => $f['id'], $environment['json']['fields'] ?? []);
        $this->assertContains($field_a_name, $field_ids, 'Ambiente deve expor os campos documentais (fieldName).');
        $this->assertContains($field_b_name, $field_ids);

        // Rascunho: campo A corrigido para inválido
        $data = (array) $scenario['slot']->getEvaluationData();
        $data[$field_a_name] = ['evaluation' => 'invalid', 'obs' => 'ilegível'];

        $draft = $this->applyCorrection($scenario['slot'], $data, draft: true);
        $this->assertEquals(200, $draft['status'], 'Rascunho em documental: ' . json_encode($draft['json']));

        /** @var RegistrationAppealReview $review */
        $review = App::i()->repo(RegistrationAppealReview::class)->find($review_id);
        $this->assertSame(RegistrationAppealReview::STATUS_DRAFT, (int) $review->status);
        $this->assertEquals('invalid', ((array) $review->correctedValue)[$field_a_name]['evaluation'], 'Rascunho persiste correctedValue por campo.');

        // Envio definitivo: slot atualizado in-place, snapshot original, SENT
        $sent = $this->applyCorrection($scenario['slot'], $data, draft: false);
        $this->assertEquals(200, $sent['status'], 'Envio em documental: ' . json_encode($sent['json']));

        $slot = App::i()->repo('RegistrationEvaluation')->find($scenario['slot']->id);
        $this->assertSame('invalid', ((array) $slot->getEvaluationData())[$field_a_name]['evaluation'], 'Slot documental atualizado in-place.');
        $this->assertSame('valid', ((array) $slot->getEvaluationData())[$field_b_name]['evaluation'], 'Campo fora da correção permanece.');
        $this->assertSame((string) RegistrationEvaluation::STATUS_SENT, (string) $slot->status);

        $review = App::i()->repo(RegistrationAppealReview::class)->find($review_id);
        $this->assertSame(RegistrationAppealReview::STATUS_SENT, (int) $review->status);
        $this->assertNotNull($review->sentTimestamp);
        $this->assertEquals('valid', ((array) $review->originalValue)[$field_a_name]['evaluation'], 'Snapshot do valor original preservado.');
    }

    /**
     * Simples: mesmo ciclo com resultado global (status + obs), sem seletor.
     */
    function testSimpleCorrectorFullCycle(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::simple);

        $created = $this->designate($scenario);
        $this->assertEquals(200, $created['status'], 'Designação em simples (F6): ' . json_encode($created['json']));
        $review_id = (int) $created['json']['id'];

        $corrector = $scenario['committeeUser'];
        $this->login($corrector);
        $this->assertTrue($scenario['slot']->canUser('modify', $corrector), 'Corretor designado deve modificar o slot simples (hook do módulo, F7).');

        $environment = $this->environment($review_id, $corrector);
        $this->assertEquals(200, $environment['status'], 'Ambiente do corretor em simples: ' . json_encode($environment['json']));
        $this->assertSame('simple', $environment['json']['method']);
        $this->assertNotEmpty($environment['json']['statusOptions'], 'Ambiente simples expõe as opções de status global.');
        $this->assertSame('3', (string) $environment['json']['originalEvaluation']['status']);

        $data = ['status' => '10', 'obs' => 'recurso deferido: selecionada'];

        $draft = $this->applyCorrection($scenario['slot'], $data, draft: true);
        $this->assertEquals(200, $draft['status'], 'Rascunho em simples: ' . json_encode($draft['json']));

        $sent = $this->applyCorrection($scenario['slot'], $data, draft: false);
        $this->assertEquals(200, $sent['status'], 'Envio em simples: ' . json_encode($sent['json']));

        $slot = App::i()->repo('RegistrationEvaluation')->find($scenario['slot']->id);
        $this->assertSame('10', (string) ((array) $slot->getEvaluationData())['status'], 'Slot simples atualizado in-place (status global).');
        $this->assertSame('recurso deferido: selecionada', ((array) $slot->getEvaluationData())['obs']);
        $this->assertSame((string) RegistrationEvaluation::STATUS_SENT, (string) $slot->status);

        $review = App::i()->repo(RegistrationAppealReview::class)->find($review_id);
        $this->assertSame(RegistrationAppealReview::STATUS_SENT, (int) $review->status);
        $this->assertEquals('3', (string) ((array) $review->originalValue)['status'], 'Snapshot do status original preservado.');
        $this->assertNotNull(App::i()->repo('Registration')->find($scenario['registration']->id)->refreshed()->consolidatedResult, 'Reconsolidação unitária executada.');
    }

    /**
     * Escopo restrito documental: campo fora do escopo com valor divergente é
     * rejeitado (400); envio somente com o campo liberado é aceito.
     */
    function testDocumentaryRestrictedScopeRejectsOutOfScopeField(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::documentary);
        [$field_a, $field_b] = array_values($scenario['fields']);
        $field_a_name = $field_a->getFieldName();
        $field_b_name = $field_b->getFieldName();

        $created = $this->designate($scenario, ['criteria' => [$field_a_name]]);
        $this->assertEquals(200, $created['status']);
        $review_id = (int) $created['json']['id'];

        $this->login($scenario['committeeUser']);

        // Ambiente: somente o campo liberado é exposto
        $environment = $this->environment($review_id, $scenario['committeeUser']);
        $field_ids = array_map(static fn (array $f): string => $f['id'], $environment['json']['fields'] ?? []);
        $this->assertEquals([$field_a_name], $field_ids, 'Escopo documental filtra os campos do ambiente.');

        // Campo B fora do escopo com valor DIVERGENTE → rejeitado
        $data = (array) $scenario['slot']->getEvaluationData();
        $data[$field_a_name] = ['evaluation' => 'invalid', 'obs' => ''];
        $data[$field_b_name] = ['evaluation' => 'invalid', 'obs' => ''];

        $rejected = $this->applyCorrection($scenario['slot'], $data, draft: false);
        $this->assertEquals(400, $rejected['status'], 'Campo fora do escopo com valor divergente deve ser rejeitado.');
        $this->assertTrue($rejected['json']['error'] ?? false);

        // Somente o campo liberado → aceito
        $data[$field_b_name] = ['evaluation' => 'valid', 'obs' => ''];
        $accepted = $this->applyCorrection($scenario['slot'], $data, draft: false);
        $this->assertEquals(200, $accepted['status'], 'Correção dentro do escopo deve ser aplicada: ' . json_encode($accepted['json']));

        $slot = App::i()->repo('RegistrationEvaluation')->find($scenario['slot']->id);
        $this->assertSame('invalid', ((array) $slot->getEvaluationData())[$field_a_name]['evaluation'], 'Campo liberado corrigido in-place.');
    }
}
