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
 * R02 (#64): snapshot do resultado preliminar por inscrição.
 *
 * Cobertura:
 * - snapshot gravado por método no ato da publicação preliminar (formato do
 *   getConsolidatedResult: técnica = valor; documental = 1/-1; simples =
 *   código do status MIN; qualificação = status do mapa valid/invalid);
 * - despublicar preliminar limpa o snapshot;
 * - publicação final não altera o snapshot (registro histórico);
 * - fidelidade histórica (decisão D1): correção aplicada APÓS a publicação
 *   preliminar muda o consolidado vigente, mas o snapshot permanece o valor
 *   capturado — inclusive via @select da API do dono.
 */
class AppealPreliminarySnapshotTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector,
        RequestFactory;

    private function enableFlags(): void
    {
        $_ENV['APPEAL_TWO_STAGE_PUBLISH'] = 'true';
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';
    }

    /**
     * Cenário por método: fase avaliativa + inscrição enviada + 2 slots
     * ENVIADOS com dados por método. Retorna [opportunity, registration].
     *
     * @return array{opportunity: Opportunity, registration: Registration, admin: User}
     */
    private function createScenario(EvaluationMethods $method, array $slot_datas): array
    {
        $this->enableFlags();

        $admin = $this->userDirector->createUser('admin');
        $valuer_a = $this->userDirector->createUser();
        $valuer_b = $this->userDirector->createUser();

        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase($method)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save();

        // Critérios pelo sub-builder config() — padrão canônico do
        // EvaluationConsolidationTest (metadata pós-save não persiste).
        if ($method === EvaluationMethods::technical) {
            $evaluation_phase_builder
                ->config()
                    ->addSection('s1', 'Seção')
                    ->addCriterion('c1', 's1', 'Critério', 0, 10, 1)
                    ->done()
                ->save();
        } elseif ($method === EvaluationMethods::qualification) {
            $evaluation_phase_builder
                ->config()
                    ->addSection('s1', 'Seção')
                    ->addCriterion('q1', 's1', 'Habilitação')
                    ->done()
                ->save();
        }

        $evaluation_phase_builder
            ->addValuer('Comissão', 'Avaliador A', $valuer_a->profile)
                ->done()
            ->addValuer('Comissão', 'Avaliador B', $valuer_b->profile)
                ->done()
            ->done()
            ->save();

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder->getInstance();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        // Critérios ANTES dos slots: setEvaluationData computa o result no
        // set (RegistrationEvaluation.php:187) — sem critérios, result null.
        foreach ($slot_datas as $index => $slot_data) {
            $user = $index === 0 ? $valuer_a : $valuer_b;

            $slot = new RegistrationEvaluation();
            $slot->registration = $registration;
            $slot->user = $user;
            $slot->status = RegistrationEvaluation::STATUS_SENT;
            $slot->setEvaluationData((object) $slot_data);
            $slot->save(true);
        }

        $app->enableAccessControl();

        return [
            'opportunity' => $opportunity,
            'registration' => $registration,
            'admin' => $admin,
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

    private function snapshotOf(Registration $registration): ?string
    {
        $fresh = App::i()->repo('Registration')->find($registration->id);

        return $fresh->preliminaryResultSnapshot;
    }

    private function publishPreliminary(Opportunity $opportunity): void
    {
        $opportunity->publishPreliminaryRegistrations();
    }

    function testTechnicalSnapshotCapturesConsolidated(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::technical, [
            ['c1' => 8],
            ['c1' => 6],
        ]);

        $expected = (string) $scenario['registration']->getEvaluationResultValue();
        $this->assertEquals('7.00', $expected, 'Consolidado técnico esperado: média de 8 e 6.');

        $this->publishPreliminary($scenario['opportunity']);

        $this->assertSame($expected, $this->snapshotOf($scenario['registration']), 'Snapshot técnico = consolidado no ato da publicação.');
    }

    function testDocumentarySnapshotCapturesConsolidated(): void
    {
        // Um slot inválido (−1) e um válido (1): AND documental → −1.
        $scenario = $this->createScenario(EvaluationMethods::documentary, [
            ['doc_a' => ['evaluation' => 'invalid', 'obs' => '']],
            ['doc_a' => ['evaluation' => 'valid', 'obs' => '']],
        ]);

        $expected = (string) $scenario['registration']->getEvaluationResultValue();
        $this->assertEquals('-1', $expected, 'Consolidado documental esperado: inválido.');

        $this->publishPreliminary($scenario['opportunity']);

        $this->assertSame($expected, $this->snapshotOf($scenario['registration']), 'Snapshot documental = 1/-1 no ato da publicação.');
    }

    function testSimpleSnapshotCapturesConsolidated(): void
    {
        // MIN dos status: 10 (selecionada) e 3 (não selecionada) → 3.
        $scenario = $this->createScenario(EvaluationMethods::simple, [
            ['status' => '10', 'obs' => ''],
            ['status' => '3', 'obs' => ''],
        ]);

        $expected = (string) $scenario['registration']->getEvaluationResultValue();
        $this->assertEquals('3', $expected, 'Consolidado simples esperado: MIN dos status.');

        $this->publishPreliminary($scenario['opportunity']);

        $this->assertSame($expected, $this->snapshotOf($scenario['registration']), 'Snapshot simples = código do status no ato da publicação.');
    }

    function testQualificationSnapshotCapturesConsolidated(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::qualification, [
            ['q1' => ['invalid']],
            ['q1' => ['valid']],
        ]);

        $expected = (string) $scenario['registration']->getEvaluationResultValue();
        $this->assertEquals('invalid', $expected, 'Consolidado de qualificação esperado: critério eliminatório inválido.');

        $this->publishPreliminary($scenario['opportunity']);

        $this->assertSame($expected, $this->snapshotOf($scenario['registration']), 'Snapshot de qualificação = status do mapa no ato da publicação.');
    }

    function testUnpublishPreliminaryClearsSnapshot(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::simple, [
            ['status' => '10', 'obs' => ''],
        ]);

        $this->publishPreliminary($scenario['opportunity']);
        $this->assertNotNull($this->snapshotOf($scenario['registration']));

        // Cada mutação em entidade fresca: os loops de snapshot usam
        // em->clear() (padrão de lote do publishRegistrations) e desanexam
        // a instância anterior — no fluxo real cada ação é um request novo.
        $fresh = App::i()->repo('Opportunity')->find($scenario['opportunity']->id);
        $fresh->unPublishPreliminaryRegistrations();

        $this->assertNull($this->snapshotOf($scenario['registration']), 'Despublicar preliminar limpa o snapshot.');
        $this->assertFalse((bool) App::i()->repo('Opportunity')->find($scenario['opportunity']->id)->publishedPreliminaryRegistrations);
    }

    function testFinalPublishDoesNotTouchSnapshot(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::simple, [
            ['status' => '8', 'obs' => ''],
        ]);

        $this->publishPreliminary($scenario['opportunity']);
        $snapshot = $this->snapshotOf($scenario['registration']);
        $this->assertSame('8', $snapshot);

        $fresh = App::i()->repo('Opportunity')->find($scenario['opportunity']->id);
        $fresh->publishRegistrations();

        $fresh_opportunity = App::i()->repo('Opportunity')->find($scenario['opportunity']->id);
        $this->assertTrue((bool) $fresh_opportunity->publishedRegistrations, 'Final publicado.');
        $this->assertSame($snapshot, $this->snapshotOf($scenario['registration']), 'Publicação final independe do snapshot (histórico preservado).');
    }

    /**
     * Decisão D1 (fidelidade histórica): correção aplicada APÓS a publicação
     * preliminar altera o consolidado vigente, mas o snapshot permanece o
     * valor do estágio preliminar — inclusive no @select da API do dono.
     */
    function testSnapshotSurvivesAppliedCorrection(): void
    {
        $scenario = $this->createScenario(EvaluationMethods::technical, [
            ['c1' => 8],
            ['c1' => 6],
        ]);

        $app = App::i();
        $admin = $scenario['admin'];

        $app->disableAccessControl();

        // Estrutura de recurso para a correção (padrão dos testes da R01)
        $opportunity = $scenario['opportunity'];
        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso';
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

        $committee_user = $this->userDirector->createUser();
        $appeal_emc->createAgentRelation($committee_user->profile, 'committee 1', true)->save(true);
        $app->enableAccessControl();

        // 1. Publica preliminar (entidade fresca): snapshot = 7.00
        $this->publishPreliminary(App::i()->repo('Opportunity')->find($opportunity->id));
        $this->assertSame('7.00', $this->snapshotOf($scenario['registration']));

        // 2. Corrige o slot A (8 → 10): consolidado vigente passa a 8
        $this->login($admin);
        $slot_a = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $scenario['registration']], ['id' => 'ASC']);

        $created = $this->dispatch(
            $this->requestFactory->POST('registrationappealreview', 'index', [], [
                'originalEvaluation' => $slot_a->id,
                'correctorUser' => $committee_user->id,
            ])
        );
        $this->assertEquals(200, $created['status'], 'Designação: ' . json_encode($created['json']));

        $this->login($committee_user);
        $sent = $this->dispatch(
            $this->requestFactory->POST('registrationevaluation', 'applyAppealCorrection', [$slot_a->id], [
                'evaluationData' => ['c1' => 10],
                'draft' => false,
            ])
        );
        $this->assertEquals(200, $sent['status'], 'Correção aplicada: ' . json_encode($sent['json']));

        // 3. Fidelidade: snapshot permanece 7; consolidado vigente = 8
        $this->login($admin);
        $fresh = $app->repo('Registration')->find($scenario['registration']->id);
        $this->assertSame('7.00', $fresh->preliminaryResultSnapshot, 'Snapshot preservado após correção (D1).');
        $this->assertEquals('8.00', (string) $fresh->getEvaluationResultValue(), 'Consolidado vigente reflete a correção in-place.');

        // 4. @select da API do dono expõe o snapshot
        $response = $this->dispatch(
            $this->requestFactory->GET('api/registration', 'find', [], [
                '@select' => 'id,preliminaryResultSnapshot',
                'id' => "EQ({$scenario['registration']->id})",
            ], ajax: true)
        );

        $this->assertEquals(200, $response['status'], 'API: ' . json_encode($response['json']));
        $this->assertSame('7.00', $response['json'][0]['preliminaryResultSnapshot'] ?? null, '@select preliminar expõe o snapshot ao dono.');
    }
}
