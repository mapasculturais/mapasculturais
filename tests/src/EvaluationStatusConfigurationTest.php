<?php

namespace Tests;

use Laminas\Diactoros\ServerRequest;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use MapasCulturais\Request as MapasRequest;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\Faker;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Garante que os status oferecidos ao avaliador (e aceitos no envio da avaliação)
 * seguem a configuração de status da fase (statusLabels da oportunidade), e que o
 * texto do resultado exibido também segue essa configuração.
 */
class EvaluationStatusConfigurationTest extends Abstract\TestCase
{
    use RequestFactory,
        UserDirector,
        Faker,
        OpportunityBuilder,
        RegistrationDirector;

    private const RENAMED_STATUS_LABELS = [
        '0' => 'Rascunho',
        '1' => 'Aguardando resposta',
        '2' => 'Deferido Parcialmente',
        '3' => 'Indeferido',
        '10' => 'Deferido',
    ];

    function testEvaluatorStatusesFollowMethodDefaultsWhenPhaseIsNotConfigured()
    {
        foreach (['simple', 'continuous'] as $type) {
            [$opportunity] = $this->createEvaluationPhase($type);

            $emc = $opportunity->evaluationMethodConfiguration;
            $expected = $emc->defaultStatuses;
            unset($expected[Registration::STATUS_DRAFT], $expected[Registration::STATUS_SENT]);

            $this->assertEquals(
                $expected,
                $emc->evaluationMethod->getEvaluatorStatuses($emc),
                "Garantindo que, sem configuração de status na fase, o avaliador da avaliação {$type} recebe os status padrão do método (sem Rascunho e Pendente)"
            );
        }
    }

    function testContinuousDefaultStatusesAreDeniedRejectedAndApproved()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous');

        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertSame(
            [Registration::STATUS_INVALID, Registration::STATUS_NOTAPPROVED, Registration::STATUS_APPROVED],
            array_keys($emc->evaluationMethod->getEvaluatorStatuses($emc)),
            'Garantindo que a avaliação contínua sem configuração continua oferecendo Negado, Indeferido e Deferido'
        );
    }

    function testEvaluatorStatusesExcludeDisabledStatusesAndUseConfiguredLabels()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido pela comissão',
            '10' => 'Deferido',
        ]);

        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertEquals(
            [3 => 'Indeferido pela comissão', 10 => 'Deferido'],
            $emc->evaluationMethod->getEvaluatorStatuses($emc),
            'Garantindo que status desabilitados não são oferecidos, Rascunho/Pendente são ignorados e os rótulos configurados são usados'
        );
    }

    function testContinuousEvaluationFormOnlyListsEnabledStatuses()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '10' => 'Deferido pela comissão',
        ]);

        $this->login($valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo que o avaliador acessa a tela de avaliação contínua');

        $config = $this->app->view->jsObject['config'];

        $this->assertEquals(
            [
                ['value' => 3, 'label' => 'Indeferido'],
                ['value' => 10, 'label' => 'Deferido pela comissão'],
            ],
            $config['continuousEvaluationForm']['statusList'],
            'Garantindo que o formulário da avaliação contínua não oferece o status Negado desabilitado na fase'
        );

        $this->assertEquals(
            [3 => 'Indeferido', 10 => 'Deferido pela comissão'],
            $config['appealPhaseChatStatuses'],
            'Garantindo que o chat do recurso oferece apenas os status habilitados na fase'
        );
    }

    function testContinuousEvaluationOffersAndAcceptsPartialApprovalWhenEnabled()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '9' => 'Deferido parcialmente',
            '10' => 'Deferido',
        ]);

        $this->login($valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo que o avaliador acessa a tela de avaliação contínua');

        $this->assertEquals(
            [
                ['value' => 3, 'label' => 'Indeferido'],
                ['value' => 9, 'label' => 'Deferido parcialmente'],
                ['value' => 10, 'label' => 'Deferido'],
            ],
            $this->app->view->jsObject['config']['continuousEvaluationForm']['statusList'],
            'Garantindo que o formulário da avaliação contínua oferece o deferido parcialmente habilitado na fase'
        );

        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_PARTIALLY_APPROVED),
            'Garantindo que a avaliação contínua aceita o deferido parcialmente habilitado na fase'
        );
    }

    function testContinuousBatchApplicationHidesDisabledPartialApproval()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '10' => 'Deferido',
        ]);

        $this->assertNotContains(
            Registration::STATUS_PARTIALLY_APPROVED,
            $this->batchApplicationStatuses($opportunity),
            'Garantindo que a aplicação em lote não ofereça o deferido parcialmente desabilitado na fase'
        );
    }

    function testContinuousBatchApplicationListsEnabledPartialApproval()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '9' => 'Deferido parcialmente',
            '10' => 'Deferido',
        ]);

        $this->assertContains(
            Registration::STATUS_PARTIALLY_APPROVED,
            $this->batchApplicationStatuses($opportunity),
            'Garantindo que a aplicação em lote ofereça o deferido parcialmente habilitado na fase'
        );
    }

    public function testConsolidatedResultLabelsUseConfiguredStatusLabel(): void
    {
        foreach (['simple', 'continuous'] as $type) {
            [$opportunity] = $this->createEvaluationPhase($type, self::RENAMED_STATUS_LABELS);

            $labels = $opportunity->evaluationMethod->getConsolidatedResultLabels($opportunity, [['evaluation' => '2']]);

            $this->assertSame(
                ['2' => 'Deferido Parcialmente'],
                $labels,
                "Garantindo que o resultado consolidado da avaliação {$type} usa o rótulo configurado na fase"
            );
        }
    }

    public function testConsolidatedResultLabelsFallBackToMethodVocabulary(): void
    {
        $expected_by_type = [
            'simple' => ['10' => 'Selecionada', '8' => 'Suplente'],
            'continuous' => ['10' => 'Deferido', '9' => 'Deferido parcialmente'],
        ];

        foreach ($expected_by_type as $type => $expected) {
            [$opportunity] = $this->createEvaluationPhase($type);

            $consolidated_results = array_map(fn ($value) => ['evaluation' => (string) $value], array_keys($expected));

            $this->assertSame(
                $expected,
                $opportunity->evaluationMethod->getConsolidatedResultLabels($opportunity, $consolidated_results),
                "Garantindo que, sem rótulo na fase, o resultado consolidado da avaliação {$type} usa o vocabulário do método"
            );
        }
    }

    public function testConsolidatedResultLabelsNameTheDraftResult(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $this->assertSame(
            ['0' => 'Rascunho em análise'],
            $opportunity->evaluationMethod->getConsolidatedResultLabels($opportunity, [['evaluation' => '0']]),
            'Garantindo que o resultado consolidado de rascunho é nomeado como em análise, não pelo rótulo do status'
        );
    }

    public function testSimpleBatchApplicationPublishesConsolidatedResultLabels(): void
    {
        $this->assertBatchApplicationResultLabel('simple', self::RENAMED_STATUS_LABELS, Registration::STATUS_INVALID, 'Deferido Parcialmente');
    }

    public function testContinuousBatchApplicationPublishesConsolidatedResultLabels(): void
    {
        $this->assertBatchApplicationResultLabel('continuous', self::RENAMED_STATUS_LABELS, Registration::STATUS_INVALID, 'Deferido Parcialmente');
    }

    public function testContinuousBatchApplicationNamesPartialApprovalWithoutConfiguredLabel(): void
    {
        $this->assertBatchApplicationResultLabel('continuous', null, Registration::STATUS_PARTIALLY_APPROVED, 'Deferido parcialmente');
    }

    public function testContinuousBatchApplicationStatusesFallBackToMethodDefaults(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', []);

        $labels = array_column(
            $this->batchApplicationConfig($opportunity, 'evaluation-method-continuous--apply')['statusList'],
            'label',
            'status'
        );

        $this->assertSame(
            'Deferido',
            $labels[Registration::STATUS_APPROVED] ?? null,
            'Garantindo que, com a configuração de status vazia, a aplicação em lote usa os status padrão do método, como o resultado consolidado'
        );
    }

    function testSimpleEvaluationFormOnlyListsEnabledStatuses()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('simple', [
            '0' => 'Rascunho',
            '1' => 'Pendente',
            '2' => 'Inválida',
            '3' => 'Não selecionada',
            '10' => 'Selecionada',
        ]);

        $this->login($valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo que o avaliador acessa a tela de avaliação simplificada');

        $this->assertEquals(
            [
                ['value' => '2', 'label' => 'Inválida'],
                ['value' => '3', 'label' => 'Não selecionada'],
                ['value' => '10', 'label' => 'Selecionada'],
            ],
            $this->app->view->jsObject['config']['simpleEvaluationForm']['statusList'],
            'Garantindo que o formulário da avaliação simplificada não oferece o status Suplente desabilitado na fase'
        );
    }

    function testContinuousEvaluationRejectsDisabledStatus()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '10' => 'Deferido',
        ]);

        $this->login($valuer);

        $this->assertStatus400(
            $this->sendEvaluation($registration, Registration::STATUS_INVALID),
            'Garantindo que a avaliação contínua não pode ser enviada com o status Negado desabilitado na fase'
        );

        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_NOTAPPROVED),
            'Garantindo que a avaliação contínua pode ser enviada com um status habilitado na fase'
        );
    }

    function testSimpleEvaluationRejectsDisabledStatus()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('simple', [
            '0' => 'Rascunho',
            '1' => 'Pendente',
            '2' => 'Inválida',
            '3' => 'Não selecionada',
            '10' => 'Selecionada',
        ]);

        $this->login($valuer);

        $this->assertStatus400(
            $this->sendEvaluation($registration, Registration::STATUS_WAITLIST),
            'Garantindo que a avaliação simplificada não pode ser enviada com o status Suplente desabilitado na fase'
        );

        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_APPROVED),
            'Garantindo que a avaliação simplificada pode ser enviada com um status habilitado na fase'
        );
    }

    function testEvaluationRejectsDraftAndPendingStatuses()
    {
        foreach (['simple', 'continuous'] as $type) {
            [, $registration, $valuer] = $this->createEvaluationPhase($type);

            $this->login($valuer);

            foreach ([Registration::STATUS_DRAFT, Registration::STATUS_SENT] as $status) {
                $this->assertStatus400(
                    $this->sendEvaluation($registration, $status),
                    "Garantindo que a avaliação {$type} não pode ser enviada com o status {$status}"
                );
            }
        }
    }

    public function testEvaluationResultStringUsesConfiguredStatusLabel(): void
    {
        foreach (['simple', 'continuous'] as $type) {
            [, $registration, $valuer] = $this->createEvaluationPhase($type, self::RENAMED_STATUS_LABELS);

            $this->login($valuer);
            $this->assertStatus200(
                $this->sendEvaluation($registration, Registration::STATUS_INVALID),
                "Garantindo que a avaliação {$type} com o status renomeado pode ser enviada"
            );

            $this->assertSame(
                'Deferido Parcialmente',
                $this->findEvaluation($registration, $valuer)->resultString,
                "Garantindo que o resultado da avaliação {$type} exibe o rótulo configurado na fase, não o padrão do método"
            );
        }
    }

    public function testEvaluationResultStringFallsBackToMethodLabelWhenPhaseIsNotConfigured(): void
    {
        foreach (['simple' => 'Inválida', 'continuous' => 'Negado'] as $type => $expected_label) {
            [, $registration, $valuer] = $this->createEvaluationPhase($type);

            $this->login($valuer);
            $this->assertStatus200(
                $this->sendEvaluation($registration, Registration::STATUS_INVALID),
                "Garantindo que a avaliação {$type} pode ser enviada numa fase sem configuração de status"
            );

            $this->assertSame(
                $expected_label,
                $this->findEvaluation($registration, $valuer)->resultString,
                "Garantindo que, sem configuração na fase, o resultado da avaliação {$type} mantém o rótulo padrão do método"
            );
        }
    }

    public function testEvaluationMethodsWithOwnVocabularyAreNotTranslatedToStatusLabel(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $evaluation_method_configuration = $opportunity->evaluationMethodConfiguration;

        $expected_by_method = [
            'technical' => [10 => 10],
            'documentary' => [1 => 'Válida', -1 => 'Inválida'],
            'qualification' => ['valid' => 'Habilitado', 'invalid' => 'Inabilitado'],
        ];

        foreach ($expected_by_method as $slug => $expected_labels) {
            $evaluation_method = $this->app->getRegisteredEvaluationMethodBySlug($slug)->evaluationMethod;

            $this->assertSame(
                [],
                $evaluation_method->getResultStatuses($evaluation_method_configuration),
                "Garantindo que a avaliação {$slug} não declara seu resultado como status de inscrição"
            );

            foreach ($expected_labels as $result => $expected_label) {
                $this->assertSame(
                    $expected_label,
                    $evaluation_method->resultToString($opportunity, $result),
                    "Garantindo que o resultado {$result} da avaliação {$slug} mantém o vocabulário próprio do método, com o tipo preservado"
                );
            }
        }
    }

    public function testReportingPhaseKeepsTheMethodVocabulary(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $this->app->disableAccessControl();
        $opportunity->isReportingPhase = true;
        $opportunity->save(true);
        $this->app->enableAccessControl();

        $opportunity = $opportunity->refreshed();
        $evaluation_method = $opportunity->getEvaluationMethod();

        $this->assertSame(
            [],
            $evaluation_method->getResultStatuses($opportunity->evaluationMethodConfiguration),
            'Garantindo que a fase de prestação de informações não declara seu resultado como status de inscrição'
        );

        $this->assertSame(
            'Deferido',
            $evaluation_method->resultToString($opportunity, Registration::STATUS_APPROVED),
            'Garantindo que a fase de prestação de informações mantém o vocabulário do método, sem resolver pelo configurado'
        );
    }

    public function testResultToStringNormalizesTheValueType(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $evaluation_method = $opportunity->getEvaluationMethod();

        $this->assertSame(
            'Deferido Parcialmente',
            $evaluation_method->resultToString($opportunity, Registration::STATUS_INVALID),
            'Garantindo que o resultado em inteiro encontra o rótulo configurado, cuja chave é string'
        );

        $this->assertSame(
            'Deferido Parcialmente',
            $evaluation_method->resultToString($opportunity, (string) Registration::STATUS_INVALID),
            'Garantindo que o resultado em string encontra o mesmo rótulo configurado'
        );
    }

    public function testTiebreakerResultIsNotTranslatedToStatusLabel(): void
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $this->assertSame(
            'Aguardando desempate',
            $opportunity->getEvaluationMethod()->resultToString($opportunity, '@tiebreaker'),
            'Garantindo que a inscrição aguardando desempate mantém o aviso, sem casar com rótulo de status'
        );
    }

    public function testFindEvaluationsPayloadUsesConfiguredStatusLabel(): void
    {
        foreach (['simple', 'continuous'] as $type) {
            [$opportunity, $registration, $valuer, $admin] = $this->createEvaluationPhase($type, self::RENAMED_STATUS_LABELS);

            $this->login($valuer);
            $this->assertStatus200(
                $this->sendEvaluation($registration, Registration::STATUS_INVALID),
                "Garantindo que a avaliação {$type} com o status renomeado pode ser enviada"
            );

            $this->login($admin);
            $result_strings = $this->resultStringsOf($this->findEvaluationsPayload($opportunity));

            $this->assertNotEmpty($result_strings, "Garantindo que o payload da avaliação {$type} traz ao menos uma avaliação");

            foreach ($result_strings as $result_string) {
                $this->assertSame(
                    'Deferido Parcialmente',
                    $result_string,
                    "Garantindo que toda linha do payload da avaliação {$type} traz o rótulo configurado na fase"
                );
            }
        }
    }

    public function testFindEvaluationsPayloadKeepsTheNotEvaluatedLiteral(): void
    {
        [$opportunity, $registration, $valuer, $admin] = $this->createEvaluationPhase('continuous', self::RENAMED_STATUS_LABELS);

        $this->login($valuer);
        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_INVALID, 'draft'),
            'Garantindo que a avaliação pode ser salva como rascunho'
        );

        $this->login($admin);

        $this->assertSame(
            ['Não avaliado'],
            $this->resultStringsOf($this->findEvaluationsPayload($opportunity)),
            'Garantindo que a avaliação ainda não enviada continua exibida como "Não avaliado", sem virar rótulo de status'
        );
    }

    public function testRegistrationEvaluationResultStringUsesConfiguredStatusLabel(): void
    {
        foreach ([self::RENAMED_STATUS_LABELS, null] as $status_labels) {
            [, $registration, $valuer, $admin] = $this->createEvaluationPhase('continuous', $status_labels);

            $this->sendFinishedEvaluation($registration, $valuer, Registration::STATUS_INVALID);
            $this->login($admin);

            $expected_label = $status_labels ? 'Deferido Parcialmente' : 'Negado';

            $this->assertSame(
                $expected_label,
                $registration->refreshed()->evaluationResultString,
                "Garantindo que o resultado consolidado da inscrição é exibido como \"{$expected_label}\", conforme a configuração da fase"
            );
        }
    }

    /**
     * Cria uma fase de avaliação com uma inscrição enviada e distribuída a um avaliador
     *
     * @return array{Opportunity, Registration, User, User}
     */
    private function createEvaluationPhase(string $type, ?array $status_labels = null): array
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
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuers(1, 'committee 1')
                ->done()
            ->getInstance();

        if ($type !== 'simple') {
            $opportunity->evaluationMethodConfiguration->type = $type;
            $opportunity->evaluationMethodConfiguration->save(true);
        }

        if ($status_labels !== null) {
            $opportunity->statusLabels = $status_labels;
            $opportunity->save(true);
        }

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $registration = $registration->refreshed();
        $valuer_user_id = array_keys($registration->valuers)[0];
        $valuer = $this->app->repo('User')->find($valuer_user_id);

        return [$opportunity->refreshed(), $registration, $valuer, $admin];
    }

    /**
     * Abre a lista de inscrições da fase como gestor e devolve os status oferecidos na aplicação em lote
     */
    private function batchApplicationStatuses(Opportunity $opportunity): array
    {
        $status_list = $this->batchApplicationConfig($opportunity, 'evaluation-method-continuous--apply')['statusList'];

        return array_map(fn ($item) => (int) $item['status'], $status_list);
    }

    private function sendFinishedEvaluation(Registration $registration, User $valuer, int $status): void
    {
        $this->login($valuer);
        $this->assertStatus200($this->sendEvaluation($registration, $status), 'Garantindo que a avaliação pode ser salva');

        $this->app->disableAccessControl();
        $evaluation = $this->findEvaluation($registration, $valuer);
        $evaluation->status = RegistrationEvaluation::STATUS_SENT;
        $evaluation->save(true);
        $this->app->enableAccessControl();
    }

    private function assertBatchApplicationResultLabel(string $type, ?array $status_labels, int $consolidated_result, string $expected_label): void
    {
        [$opportunity, $registration, , $admin] = $this->createEvaluationPhase($type, $status_labels);

        $this->app->disableAccessControl();
        $registration->consolidatedResult = (string) $consolidated_result;
        $registration->save(true);
        $this->app->enableAccessControl();

        $this->login($admin);
        $config = $this->batchApplicationConfig($opportunity, "evaluation-method-{$type}--apply");

        $this->assertSame(
            $expected_label,
            $config['resultLabels'][$consolidated_result] ?? null,
            "Garantindo que a aplicação em lote da avaliação {$type} publica o texto do resultado consolidado {$consolidated_result}"
        );
    }

    private function batchApplicationConfig(Opportunity $opportunity, string $component): array
    {
        $request = $this->requestFactory->GET('opportunity', 'registrations', [$opportunity->id]);
        $this->assertStatus200($request, 'Garantindo que o gestor acessa a lista de inscrições da fase');

        return $this->app->view->jsObject['config'][$component];
    }

    private function sendEvaluation(Registration $registration, int $status, string $evaluation_state = 'evaluated')
    {
        return $this->requestFactory->POST(
            controller_id: 'registration',
            action: 'saveEvaluation',
            url_params: [
                'id' => $registration->id,
                'status' => $evaluation_state
            ],
            payload: [
                'data' => [
                    'status' => $status,
                    'obs' => $this->faker->text()
                ]
            ]
        );
    }

    private function findEvaluation(Registration $registration, User $valuer): ?RegistrationEvaluation
    {
        return $this->app->repo('RegistrationEvaluation')->findOneBy([
            'registration' => $registration,
            'user' => $valuer,
        ]);
    }

    private function resultStringsOf(object $payload): array
    {
        return array_values(array_filter(array_map(
            fn($row) => $row['evaluation']['resultString'] ?? null,
            $payload->evaluations
        )));
    }

    private function findEvaluationsPayload(Opportunity $opportunity): object
    {
        $query = ['@opportunity' => $opportunity->id];

        $this->app->request = new MapasRequest(
            new ServerRequest(method: 'GET', uri: '/api/opportunity/findEvaluations', queryParams: $query),
            'opportunity',
            'findEvaluations',
            $query
        );

        $controller = $this->app->controller('opportunity');
        $controller->setRequestData($query + ['id' => $opportunity->id]);

        return $controller->apiFindEvaluations($opportunity->id, $query);
    }
}
