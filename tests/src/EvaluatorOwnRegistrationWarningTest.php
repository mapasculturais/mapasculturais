<?php

namespace Tests;

use Laminas\Diactoros\ServerRequest;
use MapasCulturais\App;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Cobre o aviso ao incluir/substituir avaliador que já tem inscrição no edital.
 */
class EvaluatorOwnRegistrationWarningTest extends TestCase
{
    use RequestFactory;
    use UserDirector;
    use OpportunityBuilder;
    use RegistrationBuilder;

    private function createOpportunityWithEvaluationPhase()
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
                ->setCommitteeValuersPerRegistration('committee 1', 2)
                ->save()
                ->done()
            ->getInstance();

        return [$admin, $opportunity];
    }

    private function createSentRegistrationForAgent($opportunity, $agent)
    {
        return $this->registrationBuilder
            ->reset($opportunity, $agent)
            ->fillRequiredProperties()
            ->save()
            ->send()
            ->getInstance();
    }

    private function decodeResponseJson(): array
    {
        $body = (string) App::i()->response->getBody();
        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }

    private function apiGet(string $controller, string $action, array $query_params = []): ServerRequest
    {
        $uri = '/api/' . $controller . '/' . $action;
        return new ServerRequest(method: 'GET', uri: $uri, queryParams: $query_params);
    }

    public function testBuildOwnRegistrationsWarningReturnsNullWithoutOwnRegistration(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $other = $this->userDirector->createUser();
        $this->createSentRegistrationForAgent($opportunity, $other->profile);

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $valuer = $this->userDirector->createUser();

        $warning = $emc->buildOwnRegistrationsWarning($valuer, $valuer->profile->name);

        $this->assertNull(
            $warning,
            'Sem inscrição própria, o aviso de proponente/avaliador deve ser nulo'
        );
    }

    public function testBuildOwnRegistrationsWarningWhenValuerHasOwnRegistration(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $proponent = $this->userDirector->createUser();
        $registration = $this->createSentRegistrationForAgent($opportunity, $proponent->profile);

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $warning = $emc->buildOwnRegistrationsWarning($proponent, $proponent->profile->name);

        $this->assertIsArray($warning, 'Com inscrição própria, deve haver payload de aviso');
        $this->assertSame(1, $warning['count']);
        $this->assertContains($registration->number, $warning['numbers']);
        $this->assertStringContainsString('proponente', mb_strtolower($warning['message']));
        $this->assertStringContainsString('própria', mb_strtolower($warning['message']));
        $this->assertStringContainsString('quantidades', mb_strtolower($warning['message']));
    }

    public function testBuildOwnRegistrationsWarningIgnoresDraftRegistration(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $proponent = $this->userDirector->createUser();
        $this->registrationBuilder
            ->reset($opportunity, $proponent->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $warning = $emc->buildOwnRegistrationsWarning($proponent, $proponent->profile->name);

        $this->assertNull(
            $warning,
            'Inscrição em rascunho não deve gerar aviso de inscrição própria'
        );
    }

    public function testCreateAgentRelationIncludesOwnRegistrationsWarning(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $proponent = $this->userDirector->createUser();
        $registration = $this->createSentRegistrationForAgent($opportunity, $proponent->profile);

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $response = $this->requestFactory->POST(
            'evaluationMethodConfiguration',
            'createAgentRelation',
            [$emc->id],
            [
                'group' => 'committee 1',
                'agentId' => $proponent->profile->id,
                'has_control' => true,
            ]
        );

        $this->assertStatus200(
            $response,
            'Inclusão de avaliador que também é proponente deve retornar HTTP 200'
        );

        $payload = $this->decodeResponseJson();

        $this->assertArrayHasKey(
            'ownRegistrationsWarning',
            $payload,
            'Resposta de createAgentRelation deve incluir ownRegistrationsWarning'
        );
        $this->assertSame(1, $payload['ownRegistrationsWarning']['count']);
        $this->assertContains($registration->number, $payload['ownRegistrationsWarning']['numbers']);
        $this->assertNotEmpty($payload['ownRegistrationsWarning']['message']);
    }

    public function testCreateAgentRelationOmitsWarningWithoutOwnRegistration(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $valuer = $this->userDirector->createUser();
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $response = $this->requestFactory->POST(
            'evaluationMethodConfiguration',
            'createAgentRelation',
            [$emc->id],
            [
                'group' => 'committee 1',
                'agentId' => $valuer->profile->id,
                'has_control' => true,
            ]
        );

        $this->assertStatus200(
            $response,
            'Inclusão de avaliador sem inscrição própria deve retornar HTTP 200'
        );

        $payload = $this->decodeResponseJson();

        $this->assertArrayNotHasKey(
            'ownRegistrationsWarning',
            $payload,
            'Sem inscrição própria, createAgentRelation não deve enviar ownRegistrationsWarning'
        );
    }

    public function testReplaceValuerIncludesOwnRegistrationsWarning(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $current_valuer = $this->userDirector->createUser();
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $relation = $emc->createAgentRelation($current_valuer->profile, 'committee 1', true, true, true);

        $proponent = $this->userDirector->createUser();
        $registration = $this->createSentRegistrationForAgent($opportunity, $proponent->profile);

        $response = $this->requestFactory->POST(
            'evaluationMethodConfiguration',
            'replaceValuer',
            [$emc->id],
            [
                'relation' => $relation->id,
                'newValuerAgentId' => $proponent->profile->id,
            ]
        );

        $this->assertStatus200(
            $response,
            'Substituição por avaliador que também é proponente deve retornar HTTP 200'
        );

        $payload = $this->decodeResponseJson();

        $this->assertArrayHasKey(
            'ownRegistrationsWarning',
            $payload,
            'Resposta de replaceValuer deve incluir ownRegistrationsWarning'
        );
        $this->assertSame(1, $payload['ownRegistrationsWarning']['count']);
        $this->assertContains($registration->number, $payload['ownRegistrationsWarning']['numbers']);
    }

    public function testEvaluationCommitteeApiIncludesOwnRegistrationsWarning(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithEvaluationPhase();

        $proponent = $this->userDirector->createUser();
        $registration = $this->createSentRegistrationForAgent($opportunity, $proponent->profile);

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $relation = $emc->createAgentRelation($proponent->profile, 'committee 1', true, true, true);
        $relation->status = \MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation::STATUS_ENABLED;
        $relation->save(true);

        $this->processPCache();
        $this->login($admin);

        $request = $this->apiGet('opportunity', 'evaluationCommittee', [
            '@opportunity' => $opportunity->id,
        ]);

        $this->assertStatus200(
            $request,
            'API evaluationCommittee deve retornar HTTP 200'
        );

        $payload = $this->decodeResponseJson();
        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload, 'Comissão deve listar ao menos um avaliador');

        $found = null;
        foreach ($payload as $item) {
            $agent_id = (int) ($item['agent']['id'] ?? $item['agent']->id ?? 0);
            $agent_user_id = (int) ($item['agentUserId'] ?? 0);
            if ($agent_id === (int) $proponent->profile->id || $agent_user_id === (int) $proponent->id) {
                $found = $item;
                break;
            }
        }

        $this->assertNotNull(
            $found,
            'Avaliador proponente deve aparecer na comissão. Payload: ' . json_encode($payload)
        );
        $this->assertArrayHasKey(
            'ownRegistrationsWarning',
            $found,
            'Card do avaliador na API deve incluir ownRegistrationsWarning'
        );
        $this->assertSame(1, $found['ownRegistrationsWarning']['count']);
        $this->assertContains($registration->number, $found['ownRegistrationsWarning']['numbers']);
    }
}