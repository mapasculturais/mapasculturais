<?php

namespace Tests;

use DateTime;
use DateTimeZone;
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
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Testes do recalc de computed_status no job NotifySealExpirations com
 * a estrutura genérica de conditions (spec-fe9b2cfc).
 *
 * Cobre:
 * - relevação de invalidador quando a condição não se aplica;
 * - invalidação quando a condição se aplica;
 * - invalidador sem condition continua invalidando;
 * - correção de computed_status stale no banco (bug do magic getter);
 * - job ponta-a-ponta aplicando as mesmas regras.
 */
class SealJobConditionalInvalidationTest extends TestCase
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
     * Lê computed_status persistido (coluna), evitando o magic getter
     * que chama getComputedStatus() e mascara o valor do banco.
     */
    private function getPersistedComputedStatus(int $relationId): ?string
    {
        return App::i()->em->getConnection()->fetchOne(
            'SELECT computed_status FROM seal_relation WHERE id = ?',
            [$relationId]
        );
    }

    private function createSealWithInvalidators(Agent $owner, array $lockedFields): Seal
    {
        $seal = $this->sealDirector->createSeal($owner);
        $config = [];
        foreach ($lockedFields as $field) {
            $config[$field] = [
                'hasExpiry' => true,
                'periodValue' => 1,
                'periodUnit' => 'year',
                'isInvalidator' => true,
            ];
        }
        $seal->lockedFieldsConfig = $config;
        $seal->save(true);

        return $seal;
    }

    /**
     * @return array{0: Opportunity, 1: \MapasCulturais\Entities\EvaluationMethodConfiguration}
     */
    private function createFirstPhaseOpportunityWithConditions(array $sealIds, array $conditions): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $ownerAgent = $admin->profile;
        $project = new Project();
        $project->name = 'Test Project Job Conditions';
        $project->type = 1;
        $project->owner = $ownerAgent;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($ownerAgent, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $firstPhase = $builder->getInstance()->firstPhase;

        $emcBuilder = new EvaluationPhaseBuilder($builder);
        $emcBuilder->reset($firstPhase, EvaluationMethods::documentary)
            ->fillRequiredProperties()
            ->save();

        $emc = $emcBuilder->getInstance();
        $emc->sealExemptionConfig = (object) [
            'seals' => $sealIds,
            'conditions' => $conditions,
        ];
        $emc->save(true);

        return [$firstPhase, $emc];
    }

    private function createFirstPhaseRegistration(Opportunity $firstPhase, Agent $owner): Registration
    {
        $firstPhase->registerRegistrationMetadata();

        $registration = new Registration();
        $registration->opportunity = $firstPhase;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        return $registration;
    }

    /**
     * @return \MapasCulturais\Entities\AgentSealRelation
     */
    private function relateAndExpireFields(
        Agent $agent,
        Seal $seal,
        Agent $applyingAgent,
        array $expiredFields,
        ?string $forceComputedStatus = 'invalid'
    ) {
        $relation = $agent->createSealRelation($seal, true, true, $applyingAgent);
        $app = App::i();
        $app->em->flush();
        $conn = $app->em->getConnection();

        foreach ($expiredFields as $fieldName) {
            $fieldId = $conn->fetchOne(
                'SELECT id FROM seal_relation_field WHERE seal_relation_id = :rid AND field_name = :fn',
                ['rid' => $relation->id, 'fn' => $fieldName]
            );
            if ($fieldId) {
                $conn->executeQuery(
                    'UPDATE seal_relation_field SET expiry_date = :date, notified_expire = false WHERE id = :id',
                    [
                        'date' => (new DateTime('-1 day', new DateTimeZone('UTC')))->format('Y-m-d'),
                        'id' => $fieldId,
                    ]
                );
            }
        }

        if ($forceComputedStatus !== null) {
            $conn->executeQuery(
                'UPDATE seal_relation SET computed_status = :st WHERE id = :id',
                ['st' => $forceComputedStatus, 'id' => $relation->id]
            );
        }

        return $app->repo('AgentSealRelation')->find($relation->id);
    }

    private function runExpirationJobOnce(): void
    {
        $conn = App::i()->em->getConnection();
        // Garante que o job de selos é o próximo da fila (outros jobs com due antiga roubam a vez).
        $conn->executeStatement(
            "UPDATE job
             SET status = 0,
                 next_execution_timestamp = '1999-01-01 00:00:00'
             WHERE name = 'NotifySealExpirations'"
        );
        $this->processJobs(as_date: '2100-01-01 00:00', number_of_jobs: 1);
    }

    public function testRecomputeKeepsSealValidWhenConditionNotSatisfied(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createFirstPhaseRegistration($firstPhase, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false;
        $registration->save(true);
        $app->enableAccessControl();

        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'invalid');
        $this->assertSame('invalid', $this->getPersistedComputedStatus($relation->id));

        $this->service->recomputeSealRelationComputedStatus($relation);
        $app->em->persist($relation);
        $app->em->flush();

        $this->assertSame(
            'fully_valid',
            $this->getPersistedComputedStatus($relation->id),
            'Condição não satisfeita deve relevar o invalidador e manter o selo fully_valid'
        );
    }

    public function testRecomputeInvalidatesWhenConditionSatisfied(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createFirstPhaseRegistration($firstPhase, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = true;
        $registration->save(true);
        $app->enableAccessControl();

        // Stale fully_valid no banco com campo já vencido
        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'fully_valid');
        $this->assertSame('fully_valid', $this->getPersistedComputedStatus($relation->id));

        $this->service->recomputeSealRelationComputedStatus($relation);
        $app->em->persist($relation);
        $app->em->flush();

        $this->assertSame(
            'invalid',
            $this->getPersistedComputedStatus($relation->id),
            'Condição satisfeita + invalidador vencido deve invalidar e corrigir status stale'
        );
    }

    public function testRecomputeWithoutConditionsUsesRawInvalidStatus(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], []);
        $this->createFirstPhaseRegistration($firstPhase, $owner);

        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'fully_valid');

        $this->service->recomputeSealRelationComputedStatus($relation);
        App::i()->em->persist($relation);
        App::i()->em->flush();

        $this->assertSame(
            'invalid',
            $this->getPersistedComputedStatus($relation->id),
            'Sem conditions, status bruto invalid deve ser persistido'
        );
    }

    public function testUnconditionedExpiredInvalidatorStillInvalidates(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name', 'agent.documento']);
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createFirstPhaseRegistration($firstPhase, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false; // releva agent.name
        $registration->save(true);
        $app->enableAccessControl();

        // agent.name relevado, mas agent.documento vencido sem condition → invalid
        $relation = $this->relateAndExpireFields(
            $owner,
            $seal,
            $admin->profile,
            ['agent.name', 'agent.documento'],
            'fully_valid'
        );

        $this->service->recomputeSealRelationComputedStatus($relation);
        $app->em->persist($relation);
        $app->em->flush();

        $this->assertSame(
            'invalid',
            $this->getPersistedComputedStatus($relation->id),
            'Invalidador sem condition vencido deve invalidar mesmo com outro relevado'
        );
    }

    public function testJobDoesNotInvalidateWhenConditionWaivesInvalidator(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createFirstPhaseRegistration($firstPhase, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false;
        $registration->save(true);
        $app->enableAccessControl();

        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'invalid');
        $relationId = $relation->id;

        $this->runExpirationJobOnce();

        $this->assertSame(
            'fully_valid',
            $this->getPersistedComputedStatus($relationId),
            'Job deve relevar invalidador condicionado e NÃO invalidar o selo'
        );
    }

    public function testJobInvalidatesWhenConditionApplies(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        $conditions = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];

        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], $conditions);
        $registration = $this->createFirstPhaseRegistration($firstPhase, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = true;
        $registration->save(true);
        $app->enableAccessControl();

        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'fully_valid');
        $relationId = $relation->id;

        $this->runExpirationJobOnce();

        $this->assertSame(
            'invalid',
            $this->getPersistedComputedStatus($relationId),
            'Job deve invalidar quando a condição se aplica e o invalidador está vencido'
        );
    }

    public function testJobCorrectsStaleFullyValidEvenWhenAlreadyNotified(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithInvalidators($admin->profile, ['agent.name']);
        // Sem conditions → invalid bruto
        [$firstPhase] = $this->createFirstPhaseOpportunityWithConditions([$seal->id], []);
        $this->createFirstPhaseRegistration($firstPhase, $owner);

        $relation = $this->relateAndExpireFields($owner, $seal, $admin->profile, ['agent.name'], 'fully_valid');
        $relationId = $relation->id;

        // Simula campo já notificado (job antigo só recalculava com notified_expire=false)
        $app = App::i();
        $app->em->getConnection()->executeQuery(
            'UPDATE seal_relation_field SET notified_expire = true WHERE seal_relation_id = ?',
            [$relationId]
        );

        $this->runExpirationJobOnce();

        $this->assertSame(
            'invalid',
            $this->getPersistedComputedStatus($relationId),
            'Pass de recompute do job deve corrigir fully_valid stale mesmo com notified_expire=true'
        );
    }
}
