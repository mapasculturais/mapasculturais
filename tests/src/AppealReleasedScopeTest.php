<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
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
 * F6 (#49): abertura de elegibilidade para todos os métodos + seletor de
 * escopo de critérios (Variante 3, override registrado na #7 em 2026-09-09).
 *
 * Cobertura:
 * - AC1: método NÃO-técnico (simple) tem designação aceita e
 *   eligibleCorrectors() devolve dono do slot + Comissão de Recursos;
 * - AC2: releasedScope com criteria explícito e VAZIO → 400 no POST;
 * - AC3: POST sem releasedScope persiste null (correção integral) e o PATCH
 *   com releasedScope null LIMPA restrição existente.
 */
class AppealReleasedScopeTest extends TestCase
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
     * Cenário análogo ao de AppealAssignmentApiTest, com método parametrizável.
     *
     * @return array{admin: User, slotOwner: User, committeeUser: User,
     *   opportunity: Opportunity, registration: Registration,
     *   slot: RegistrationEvaluation, appealPhase: Opportunity}
     */
    private function createScenario(EvaluationMethods $method = EvaluationMethods::simple): array
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
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $slot = new RegistrationEvaluation();
        $slot->registration = $registration;
        $slot->user = $slot_owner;
        $slot->status = RegistrationEvaluation::STATUS_EVALUATED;
        $slot->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso ' . $method->value;
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
    private function postAssignment(array $payload): array
    {
        return $this->dispatch(
            $this->requestFactory->POST('registrationappealreview', 'index', [], $payload)
        );
    }

    /**
     * @return array{status: int, json: mixed}
     */
    private function patchAssignment(int $id, array $payload): array
    {
        return $this->dispatch(
            $this->requestFactory->PATCH('registrationappealreview', 'single', [$id], $payload)
        );
    }

    /**
     * AC1 (F6): com a fase principal em método SIMPLES (não-técnico), a
     * designação é aceita — elegibilidade aberta a todos os métodos — e
     * eligibleCorrectors() devolve dono do slot + Comissão de Recursos.
     */
    function testNonTechnicalMethodIsEligibleForDesignation(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::simple);
        $this->login($scenario['admin']);

        // eligibleCorrectors: dono do slot + comissão (sem early-return técnico)
        $probe = new RegistrationAppealReview();
        $probe->originalEvaluation = $scenario['slot'];
        $probe->registration = $scenario['registration'];
        $probe->appealPhase = $scenario['appealPhase'];
        $probe->slotOwnerUser = $scenario['slotOwner'];
        $probe->correctorUser = $scenario['committeeUser'];

        $eligible_ids = array_map(
            static fn (User $user): int => (int) $user->id,
            $probe->eligibleCorrectors($scenario['slot'])
        );

        $this->assertContains($scenario['slotOwner']->id, $eligible_ids, 'Dono do slot deve ser elegível em método não-técnico (F6).');
        $this->assertContains($scenario['committeeUser']->id, $eligible_ids, 'Comissão de Recursos deve ser elegível em método não-técnico (F6).');
        $this->assertCount(2, $eligible_ids, 'Elegibilidade em método não-técnico: dono + comissão, sem duplicatas.');

        $response = $this->postAssignment([
            'originalEvaluation' => $scenario['slot']->id,
            'correctorUser' => $scenario['committeeUser']->id,
        ]);

        $this->assertEquals(200, $response['status'], 'Designação em método não-técnico deve ser criada (F6): ' . json_encode($response['json']));

        /** @var RegistrationAppealReview $review */
        $review = App::i()->repo(RegistrationAppealReview::class)->find($response['json']['id']);
        $this->assertNull($review->releasedScope, 'Sem seletor (método simples) o escopo persiste null = tudo liberado.');
    }

    /**
     * AC2 (F6): releasedScope com criteria vazio é rejeitado com 400 e
     * mensagem clara — no POST e no PATCH.
     */
    function testEmptyCriteriaScopeIsRejected(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::technical);
        $this->login($scenario['admin']);

        $post_response = $this->postAssignment([
            'originalEvaluation' => $scenario['slot']->id,
            'correctorUser' => $scenario['committeeUser']->id,
            'releasedScope' => json_encode(['criteria' => []]),
        ]);

        $this->assertEquals(400, $post_response['status'], 'POST com criteria vazio deve ser rejeitado (F6).');
        $this->assertTrue($post_response['json']['error'] ?? false);
        $this->assertStringContainsStringIgnoringCase('escopo', (string) $post_response['json']['data'], 'Mensagem deve explicar a rejeição do escopo vazio.');

        // Cria designação válida para exercitar o PATCH
        $created = $this->postAssignment([
            'originalEvaluation' => $scenario['slot']->id,
            'correctorUser' => $scenario['committeeUser']->id,
        ]);
        $this->assertEquals(200, $created['status']);

        $patch_response = $this->patchAssignment((int) $created['json']['id'], [
            'releasedScope' => json_encode(['criteria' => []]),
        ]);

        $this->assertEquals(400, $patch_response['status'], 'PATCH com criteria vazio deve ser rejeitado (F6).');
        $this->assertTrue($patch_response['json']['error'] ?? false);
    }

    /**
     * AC3 (F6): POST sem releasedScope persiste null (integral) e o PATCH com
     * releasedScope null LIMPA a restrição previamente gravada.
     */
    function testNullScopeMeansUnrestrictedAndClearsRestrictionOnPatch(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::technical);
        $this->login($scenario['admin']);

        // Restrição parcial
        $created = $this->postAssignment([
            'originalEvaluation' => $scenario['slot']->id,
            'correctorUser' => $scenario['committeeUser']->id,
            'releasedScope' => json_encode(['criteria' => ['c-1', 'c-2']]),
        ]);
        $this->assertEquals(200, $created['status']);

        /** @var RegistrationAppealReview $review */
        $review = App::i()->repo(RegistrationAppealReview::class)->find($created['json']['id']);
        $this->assertEquals(['criteria' => ['c-1', 'c-2']], (array) $review->releasedScope);

        // "Todos marcados" na substituição → payload null → LIMPA a restrição
        $patch_response = $this->patchAssignment((int) $created['json']['id'], [
            'correctorUser' => $scenario['slotOwner']->id,
            'releasedScope' => null,
        ]);
        $this->assertEquals(200, $patch_response['status'], 'PATCH com releasedScope null deve limpar a restrição: ' . json_encode($patch_response['json']));

        $review = App::i()->repo(RegistrationAppealReview::class)->find($created['json']['id']);
        $this->assertNull($review->releasedScope, 'releasedScope null após PATCH = todos os critérios liberados.');
        $this->assertEquals($scenario['slotOwner']->id, $review->correctorUser->id);
    }
}
