<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\SealRelation;
use SealExemption\SealExemptionVerifier;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Tests for SealExemptionVerifier — actual behavior (SQL against seal_relation).
 */
class SealExemptionVerifierTest extends TestCase
{
    use AgentDirector;
    use SealDirector;
    use UserDirector;

    private SealExemptionVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new SealExemptionVerifier();
    }

    /**
     * Helper: create an agent and a seal, relate them, then force the
     * seal_relation.computed_status to the desired value.
     */
    private function createSealRelationWithStatus(string $computedStatus): array
    {
        $app = App::i();
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $agent = $this->agentDirector->createAgent($admin->profile);

        $agent->createSealRelation($seal, true, true, $admin->profile);

        $this->setComputedStatus($agent, $seal, $computedStatus);

        return [$agent, $seal];
    }

    /**
     * Force computed_status on the relation row (avoids having to configure
     * seal field expiry dates just to test the verifier predicate).
     */
    private function setComputedStatus(Agent $agent, Seal $seal, string $status): void
    {
        $app = App::i();
        $app->em->getConnection()->executeQuery(
            "UPDATE seal_relation
             SET computed_status = :status
             WHERE object_type = :object_type
               AND object_id = :agent_id
               AND seal_id = :seal_id",
            [
                'status'       => $status,
                'object_type'  => 'MapasCulturais\Entities\Agent',
                'agent_id'     => $agent->id,
                'seal_id'      => $seal->id,
            ]
        );
    }

    /**
     * Set the relation row status (enabled=1 vs pending=-5).
     */
    private function setRelationStatus(Agent $agent, Seal $seal, int $status): void
    {
        $app = App::i();
        $app->em->getConnection()->executeQuery(
            "UPDATE seal_relation
             SET status = :status
             WHERE object_type = :object_type
               AND object_id = :agent_id
               AND seal_id = :seal_id",
            [
                'status'      => $status,
                'object_type' => 'MapasCulturais\Entities\Agent',
                'agent_id'    => $agent->id,
                'seal_id'     => $seal->id,
            ]
        );
    }

    public function testAllConfiguredSealsFullyValidReturnsTrue(): void
    {
        [$agent, $seal1] = $this->createSealRelationWithStatus('fully_valid');
        [$agent2, $seal2] = $this->createSealRelationWithStatus('fully_valid');

        // reuse the same agent for both seals
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $agent2->createSealRelation($seal1, true, true, $admin->profile);
        $this->setComputedStatus($agent2, $seal1, 'fully_valid');

        $this->assertTrue($this->verifier->hasAllValidSeals($agent2, [$seal1->id, $seal2->id]));
    }

    public function testSomeSealsFullyValidReturnsFalse(): void
    {
        [$agent, $seal1] = $this->createSealRelationWithStatus('fully_valid');
        $seal2 = $this->sealDirector->createSeal($this->userDirector->createUser('admin')->profile);

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, [$seal1->id, $seal2->id]));
    }

    public function testPartiallyValidSealReturnsFalse(): void
    {
        [$agent, $seal] = $this->createSealRelationWithStatus('partially_valid');

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, [$seal->id]));
    }

    public function testInvalidSealReturnsFalse(): void
    {
        [$agent, $seal] = $this->createSealRelationWithStatus('invalid');

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, [$seal->id]));
    }

    public function testPendingRelationStatusReturnsFalse(): void
    {
        [$agent, $seal] = $this->createSealRelationWithStatus('fully_valid');
        $this->setRelationStatus($agent, $seal, SealRelation::STATUS_PENDING);

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, [$seal->id]));
    }

    public function testDisabledRelationStatusReturnsFalse(): void
    {
        [$agent, $seal] = $this->createSealRelationWithStatus('fully_valid');
        $this->setRelationStatus($agent, $seal, -10);

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, [$seal->id]));
    }

    public function testEmptySealListReturnsFalse(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $agent = $this->agentDirector->createAgent($admin->profile);

        $this->assertFalse($this->verifier->hasAllValidSeals($agent, []));
    }

    public function testFindAgentsWithAllValidSealsReturnsOnlyMatchingAgents(): void
    {
        [$agent1, $seal1] = $this->createSealRelationWithStatus('fully_valid');
        [$agent2, $seal2] = $this->createSealRelationWithStatus('fully_valid');
        [$agent3, $seal3] = $this->createSealRelationWithStatus('partially_valid');

        // agent1 also has seal2 fully_valid
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $agent1->createSealRelation($seal2, true, true, $admin->profile);
        $this->setComputedStatus($agent1, $seal2, 'fully_valid');

        $result = $this->verifier->findAgentsWithAllValidSeals(
            [$agent1->id, $agent2->id, $agent3->id],
            [$seal1->id, $seal2->id]
        );

        $this->assertSame([$agent1->id], $result);
    }
}
