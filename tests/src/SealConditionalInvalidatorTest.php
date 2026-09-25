<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Seal;
use SealExemption\ProponentAgentResolver;
use SealExemption\SealExemptionService;
use SealExemption\SealExemptionVerifier;
use Tests\Abstract\TestCase;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Testes da condicionalidade de invalidadores na isenção por selos (spec-fe9b2cfc).
 *
 * Cobre os caminhos críticos:
 *  - Condição satisfeita → invalidador aplica (não isento).
 *  - Condição não satisfeita → invalidador relevado (isento se demais válidos).
 *  - Campo condicional em branco → invalida (não isento).
 *  - Sem conditions → legado bit-a-bit.
 *  - Validação estrutural do schema conditions.
 */
class SealConditionalInvalidatorTest extends TestCase
{
    use AgentDirector;
    use OpportunityBuilder;
    use SealDirector;
    use UserDirector;

    private SealExemptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SealExemptionService(
            new ProponentAgentResolver(),
            new SealExemptionVerifier()
        );
    }

    /**
     * Cria um selo com lockedFieldsConfig contendo um invalidador.
     */
    private function createSealWithInvalidator(Agent $owner, string $lockedField = 'agent.name'): Seal
    {
        $seal = $this->sealDirector->createSeal($owner);
        $seal->lockedFieldsConfig = [
            $lockedField => [
                'hasExpiry' => true,
                'periodValue' => 1,
                'periodUnit' => 'year',
                'isInvalidator' => true,
            ],
        ];
        $seal->save(true);
        return $seal;
    }

    /**
     * Relaciona o selo ao agente e força o SealRelationField do invalidador
     * para vencido, e o computed_status para 'invalid'.
     */
    private function relateSealAndExpireInvalidator(Agent $agent, Seal $seal, Agent $applyingAgent, string $lockedField = 'agent.name'): void
    {
        $relation = $agent->createSealRelation($seal, true, true, $applyingAgent);

        $app = App::i();
        $app->em->flush();
        $conn = $app->em->getConnection();

        // Encontrar o SealRelationField do invalidador e setar vencido
        $fieldId = $conn->fetchOne(
            "SELECT id FROM seal_relation_field WHERE seal_relation_id = :rid AND field_name = :fn",
            ['rid' => $relation->id, 'fn' => $lockedField]
        );

        if ($fieldId) {
            $conn->executeQuery(
                'UPDATE seal_relation_field SET expiry_date = :date WHERE id = :id',
                [
                    'date' => (new \DateTime('-1 day', new \DateTimeZone('UTC')))->format('Y-m-d'),
                    'id' => $fieldId,
                ]
            );
        }

        // Forçar computed_status = 'invalid' (o campo vencido+invalidador invalida)
        $conn->executeQuery(
            "UPDATE seal_relation SET computed_status = 'invalid'
             WHERE object_type = :ot AND object_id = :aid AND seal_id = :sid",
            [
                'ot' => 'agentsealrelation',
                'aid' => $agent->id,
                'sid' => $seal->id,
            ]
        );

        // NOTA: não fazer em->clear() global — quebra o cascade das entidades do teste.
        // O SQL acima já é persistido diretamente; as entidades em memória só precisam
        // ser refreshadas pontualmente onde o teste lê o novo estado.
    }

    /**
     * Cria oportunidade com config de isenção e conditions.
     */
    private function createOpportunityWithConditions(array $sealIds, array $conditions = null): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $project = new Project();
        $project->name = 'Test Project';
        $project->type = 1;
        $project->owner = $owner;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($owner, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $config = ['seals' => $sealIds];
        if ($conditions !== null) {
            $config['conditions'] = $conditions;
        }
        $emc->sealExemptionConfig = (object) $config;
        $emc->save(true);

        return $emc->opportunity;
    }

    private function createPhaseRegistration(Opportunity $opportunity, Agent $owner): Registration
    {
        $opportunity->registerRegistrationMetadata();

        $registration = new Registration();
        $registration->opportunity = $opportunity;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->previousPhaseRegistrationId = 999999;
        $registration->save(true);

        return $registration;
    }

    /**
     * SEM conditions → comportamento legado: selo invalido → não isento.
     */
    public function testLegacyWithoutConditionsNotExemptWhenSealInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidator($admin->profile);
        $opportunity = $this->createOpportunityWithConditions([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);
        // Expirar DEPOIS de criar a registration (evita em->clear() desconectar entidades)
        $this->relateSealAndExpireInvalidator($owner, $seal, $admin->profile);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();
        // Legado: não isento (status não é APPROVED)
        $this->assertNotSame(Registration::STATUS_APPROVED, $registration->status);
    }

    /**
     * Condição NÃO satisfeita (appliedForQuota ausente/diferente) → invalidador relevado → ISENTO.
     */
    public function testConditionNotSatisfiedWaivesInvalidatorAndGrantsExemption(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidator($admin->profile, 'agent.name');

        // Configura condição: o invalidador agent.name só exige se appliedForQuota = "1"
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        $opportunity = $this->createOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createPhaseRegistration($opportunity, $owner);
        $this->relateSealAndExpireInvalidator($owner, $seal, $admin->profile, 'agent.name');

        // Proponente NÃO marcou cota (appliedForQuota = false/"0") → condição não satisfeita
        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false;
        $registration->save(true);
        $app->enableAccessControl();

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();
        // Condição não satisfeita → relevado → isento
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
    }

    /**
     * Condição satisfeita (appliedForQuota = "1") → invalidador aplica → NÃO isento.
     */
    public function testConditionSatisfiedDoesNotWaiveAndNotExempt(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidator($admin->profile, 'agent.name');

        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        $opportunity = $this->createOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createPhaseRegistration($opportunity, $owner);
        $this->relateSealAndExpireInvalidator($owner, $seal, $admin->profile, 'agent.name');

        // Proponente marcou cota → condição satisfeita → invalidador aplica
        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = true;
        $registration->save(true);
        $app->enableAccessControl();

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();
        // Condição satisfeita → invalidador vencido aplica → não isento
        $this->assertNotSame(Registration::STATUS_APPROVED, $registration->status);
    }

    /**
     * Campo condicional em BRANCO (metadata string não-preenchida) → invalida.
     *
     * Usa uma metadata de texto que pode ficar genuinamente vazia (diferente de
     * appliedForQuota, que tem default=false e portanto nunca é null de fato).
     */
    public function testBlankConditionalFieldInvalidates(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidator($admin->profile, 'agent.name');

        // Condição sobre um campo de texto fictício field_testQuota que permanece vazio
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'field_testQuota', 'values' => ['Sim']],
                    ],
                ],
            ],
        ];

        $opportunity = $this->createOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createPhaseRegistration($opportunity, $owner);
        $this->relateSealAndExpireInvalidator($owner, $seal, $admin->profile, 'agent.name');

        // NÃO setar field_testQuota → permanece null/vazio → branco → invalida
        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();
        // Branco → invalida → não isento
        $this->assertNotSame(Registration::STATUS_APPROVED, $registration->status);
    }

    /**
     * Validação estrutural: conditions com clause sem values → erro.
     */
    public function testValidateConditionsStructureRejectsEmptyValues(): void
    {
        $badConfig = [
            'seals' => [1],
            'conditions' => [
                '1' => [
                    'agent.name' => [
                        'clauses' => [
                            ['field' => 'appliedForQuota', 'values' => []],
                        ],
                    ],
                ],
            ],
        ];

        $error = SealExemptionService::validateConditionsStructure($badConfig);
        $this->assertNotNull($error);
    }

    /**
     * Validação estrutural: conditions válido → null (sem erro).
     */
    public function testValidateConditionsStructureAcceptsValid(): void
    {
        $goodConfig = [
            'seals' => [1],
            'conditions' => [
                '1' => [
                    'agent.name' => [
                        'clauses' => [
                            ['field' => 'appliedForQuota', 'values' => ['1']],
                        ],
                    ],
                ],
            ],
        ];

        $error = SealExemptionService::validateConditionsStructure($goodConfig);
        $this->assertNull($error);
    }
}
