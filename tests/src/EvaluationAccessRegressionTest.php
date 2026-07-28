<?php

namespace Tests;

use Laminas\Diactoros\ServerRequest;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Request as MapasRequest;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\Faker;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Regressão das correções de avaliação (caso Vinícius / gestor-avaliador):
 * - filtro findEvaluations com @evaluationId para gestor
 * - bloqueio de autoavaliação (UI + canUser evaluate)
 * - Voltar preserva lista de origem
 * - guards de código-fonte (links com user:, @evaluationId no load)
 */
class EvaluationAccessRegressionTest extends TestCase
{
    use RequestFactory;
    use UserDirector;
    use Faker;
    use OpportunityBuilder;
    use RegistrationDirector;
    use RegistrationBuilder;

    private function createOpportunityWithCommittee(int $valuers = 2, int $valuers_per_registration = 1)
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
                ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registration)
                ->save()
                ->addValuers($valuers, 'committee 1')
                ->done()
            ->getInstance();

        return [$admin, $opportunity];
    }

    private function getAssignedValuer(Registration $registration, $opportunity)
    {
        $valuer_user_id = array_keys($registration->valuers)[0];
        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];
        foreach ($committee as $valuer) {
            if ($valuer->user->id == $valuer_user_id) {
                return $valuer->user->refreshed();
            }
        }
        $this->fail('Avaliador atribuído não encontrado em valuers');
    }

    private function callFindEvaluations(int $opportunity_id, array $query): object
    {
        $app = App::i();
        $psr = new ServerRequest(
            method: 'GET',
            uri: '/api/opportunity/findEvaluations',
            queryParams: $query
        );
        $app->request = new MapasRequest($psr, 'opportunity', 'findEvaluations', $query);

        $controller = $app->controller('opportunity');
        $controller->setRequestData($query + ['id' => $opportunity_id]);

        return $controller->apiFindEvaluations($opportunity_id, $query);
    }

    private function responseBody(): string
    {
        return (string) App::i()->response->getBody();
    }

    public function testManagerFindEvaluationsWithoutFilterReturnsWholeCommittee(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee(valuers: 2, valuers_per_registration: 1);

        $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $this->login($admin);

        $result = $this->callFindEvaluations($opportunity->id, [
            '@opportunity' => $opportunity->id,
        ]);

        $valuer_user_ids = array_unique(array_map(
            fn($row) => (int) ($row['valuer']['user'] ?? $row['evaluation']['user'] ?? 0),
            $result->evaluations
        ));

        $this->assertGreaterThanOrEqual(
            2,
            count(array_filter($valuer_user_ids)),
            'Sem @evaluationId, gestor deve ver avaliações de mais de um avaliador da comissão'
        );
        $this->assertGreaterThanOrEqual(2, (int) $result->count);
    }

    public function testManagerFindEvaluationsWithEvaluationIdFiltersToThatValuer(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee(valuers: 2, valuers_per_registration: 1);

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer = $this->getAssignedValuer($registration, $opportunity);
        $this->login($admin);

        $result = $this->callFindEvaluations($opportunity->id, [
            '@opportunity' => $opportunity->id,
            '@evaluationId' => (string) $valuer->id,
        ]);

        $this->assertGreaterThan(0, count($result->evaluations), 'Filtro @evaluationId deve retornar linhas');

        foreach ($result->evaluations as $row) {
            $row_user = (int) ($row['valuer']['user'] ?? 0);
            $this->assertSame(
                (int) $valuer->id,
                $row_user,
                'Com @evaluationId, todas as linhas devem ser do avaliador filtrado'
            );
        }
    }

    public function testCanUserEvaluateIsFalseForOwnRegistrationEvenIfForcedInValuers(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee(valuers: 1, valuers_per_registration: 2);
        $this->login($admin);

        $proponent = $this->userDirector->createUser();
        $registration = $this->registrationBuilder
            ->reset($opportunity, $proponent->profile)
            ->fillRequiredProperties()
            ->save()
            ->send()
            ->getInstance();

        $opportunity->evaluationMethodConfiguration->createAgentRelation(
            agent: $proponent->profile,
            group: 'committee 1',
            has_control: true,
            save: true,
            flush: true
        );
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        // Simula include manual indevido em valuers
        $app = App::i();
        $app->disableAccessControl();
        $valuers = $registration->valuers;
        $valuers[$proponent->id] = 'committee 1';
        $reflection = new \ReflectionProperty($registration, '__valuers');
        $reflection->setAccessible(true);
        $reflection->setValue($registration, (object) $valuers);
        $registration->save(true);
        $app->enableAccessControl();

        $registration = $registration->refreshed();
        $this->assertTrue(isset($registration->valuers[$proponent->id]), 'Pré-condição: forçado em valuers');
        $this->assertFalse(
            $registration->canUser('evaluate', $proponent),
            'Proponente não pode evaluate a própria inscrição mesmo se estiver em valuers'
        );
    }

    public function testEvaluationPageShowsOwnRegistrationNotice(): void
    {
        // Dono do edital também é proponente (caso Vinícius)
        [$admin, $opportunity] = $this->createOpportunityWithCommittee(valuers: 1, valuers_per_registration: 2);

        $registration = $this->registrationBuilder
            ->reset($opportunity, $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->send()
            ->getInstance();

        $opportunity->evaluationMethodConfiguration->createAgentRelation(
            agent: $admin->profile,
            group: 'committee 1',
            has_control: true,
            save: true,
            flush: true
        );

        $this->login($admin);

        $request = $this->requestFactory->GET(
            'registration',
            'evaluation',
            [
                'id' => $registration->id,
                'user' => $admin->id,
            ]
        );

        $this->assertStatus200($request, 'Dono do edital deve abrir a tela da própria inscrição');
        $body = $this->responseBody();

        $this->assertStringContainsString(
            'evaluation-form__own-registration-notice',
            $body,
            'Tela da própria inscrição deve exibir o aviso de autoavaliação'
        );
        $this->assertStringContainsString(
            '<fieldset disabled>',
            $body,
            'Formulário da própria inscrição deve estar em fieldset disabled'
        );
        // O texto passa por i::__(); aceita variações de encoding/acentos
        $this->assertTrue(
            str_contains($body, 'demais membros')
                || str_contains($body, 'própria inscrição')
                || str_contains($body, 'propria inscricao')
                || preg_match('/autoavalia|pr[oó]pria/iu', $body),
            'Texto do aviso de autoavaliação deve aparecer no HTML'
        );
    }

    public function testEvaluationPageDoesNotShowOwnRegistrationNoticeForOtherValuer(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer = $this->getAssignedValuer($registration, $opportunity);
        $this->login($valuer);

        $request = $this->requestFactory->GET(
            'registration',
            'evaluation',
            [
                'id' => $registration->id,
                'user' => $valuer->id,
            ]
        );

        $this->assertStatus200($request, 'Avaliador atribuído deve abrir a tela');
        $body = $this->responseBody();

        $this->assertStringNotContainsString(
            'evaluation-form__own-registration-notice',
            $body,
            'Avaliador de inscrição alheia não deve ver aviso de autoavaliação'
        );
    }

    public function testBackButtonUsesListaDeAvaliacoesReferer(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer = $this->getAssignedValuer($registration, $opportunity);
        $list_url = App::i()->createUrl('opportunity', 'allEvaluations', [$opportunity->id]);

        unset($_SESSION["evaluationBackUrl:{$opportunity->id}"]);
        $this->login($admin);

        $request = $this->requestFactory->GET(
            'registration',
            'evaluation',
            [
                'id' => $registration->id,
                'user' => $valuer->id,
            ],
            [],
            ['Referer' => [$list_url]]
        );

        $this->assertStatus200($request, 'Gestor deve abrir avaliação de outro avaliador');
        $body = $this->responseBody();

        $this->assertStringContainsString(
            htmlspecialchars($list_url),
            $body,
            'Voltar deve apontar para lista-de-avaliacoes quando essa foi a origem'
        );
        $this->assertSame(
            $list_url,
            $_SESSION["evaluationBackUrl:{$opportunity->id}"] ?? null,
            'Sessão deve gravar a lista gerencial como origem'
        );
    }

    public function testBackButtonFallbackToAllEvaluationsWhenManagerViewsOtherValuer(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer = $this->getAssignedValuer($registration, $opportunity);
        $all_url = App::i()->createUrl('opportunity', 'allEvaluations', [$opportunity->id]);

        unset($_SESSION["evaluationBackUrl:{$opportunity->id}"]);
        $this->login($admin);

        $request = $this->requestFactory->GET(
            'registration',
            'evaluation',
            [
                'id' => $registration->id,
                'user' => $valuer->id,
            ]
        );

        $this->assertStatus200($request);
        $body = $this->responseBody();

        $this->assertStringContainsString(
            htmlspecialchars($all_url),
            $body,
            'Sem referer, gestor vendo avaliação de outro deve voltar para lista gerencial'
        );
    }

    private function projectSrcPath(string $relative): string
    {
        // Local: tests/src → ../../src ; Docker (mount tests/src → /var/www/tests): ../src
        $candidates = [
            dirname(__DIR__, 2) . '/src/' . $relative,
            dirname(__DIR__) . '/src/' . $relative,
            '/var/www/src/' . $relative,
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        $this->fail("Arquivo de fonte não encontrado: {$relative}");
    }

    public function testSourceGuardsForEvaluatorListFilterAndSidebarUserLinks(): void
    {
        $table_js = file_get_contents($this->projectSrcPath('modules/Opportunities/components/opportunity-evaluations-table/script.js'));
        $list_js = file_get_contents($this->projectSrcPath('modules/Opportunities/components/opportunity-evaluations-list/script.js'));
        $eval_view = file_get_contents($this->projectSrcPath('modules/Opportunities/views/registration/evaluation.php'));
        $eval_form = file_get_contents($this->projectSrcPath('modules/Opportunities/components/evaluation-form/template.php'));

        $this->assertStringContainsString(
            "query['@evaluationId'] = `\${this.user}`",
            $table_js,
            'Lista do avaliador deve enviar @evaluationId no load'
        );
        $this->assertStringContainsString(
            'showEvaluatorFilter',
            $table_js,
            'Filtro de avaliador só na lista gerencial'
        );
        $this->assertStringContainsString(
            'user: userEvaluatorId',
            $list_js,
            'Sidebar deve montar URL com user do avaliador'
        );
        $this->assertStringContainsString(
            'evaluationBackUrl:',
            $eval_view,
            'Tela de avaliação deve guardar origem do Voltar na sessão'
        );
        $this->assertStringContainsString(
            'evaluation-form__own-registration-notice',
            $eval_form,
            'Aviso de autoavaliação deve existir no formulário'
        );
    }

    public function testSaveUserEvaluationStillDeniedOutsideValuers(): void
    {
        // Smoke complementar ao EvaluationSavePermissionTest — garante assertCanSaveUserEvaluation no caminho direto
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $this->login($admin);

        $this->expectException(\MapasCulturais\Exceptions\PermissionDenied::class);
        $registration->saveUserEvaluation([
            'status' => Registration::STATUS_WAITLIST,
            'obs' => 'tentativa ilegal',
        ], $admin);
    }
}
