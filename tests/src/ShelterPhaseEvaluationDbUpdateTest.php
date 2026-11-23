<?php

namespace Tests;

use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use OpportunityPhases\Module as OpportunityPhasesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class ShelterPhaseEvaluationDbUpdateTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testConfigurationMovesToDataCollectionAndEmptyPhaseIsRemoved(): void
    {
        [$root, $shelter, $configuration] = $this->createShelterStructure();
        $this->registrationDirector->createSentRegistrations($root, 2);

        $this->moveConfigurations();

        $this->assertSame($root->id, $this->configurationOwnerId($configuration->id));
        $this->assertFalse($this->opportunityExists($shelter->id));
    }

    function testPhaseWithRegistrationIsIgnored(): void
    {
        [, $shelter, $configuration] = $this->createShelterStructure();
        $this->registrationDirector->createDraftRegistrations($shelter, 1);

        $this->moveConfigurations();

        $this->assertSame($shelter->id, $this->configurationOwnerId($configuration->id));
        $this->assertTrue($this->opportunityExists($shelter->id));
    }

    function testSecondRunChangesNothing(): void
    {
        [$root, $shelter, $configuration] = $this->createShelterStructure();

        $this->moveConfigurations();
        $phasesAfterFirstRun = $this->childrenIds($root->id);

        $this->moveConfigurations();

        $this->assertSame($root->id, $this->configurationOwnerId($configuration->id));
        $this->assertFalse($this->opportunityExists($shelter->id));
        $this->assertSame($phasesAfterFirstRun, $this->childrenIds($root->id));
    }

    /** @return array{Opportunity, Opportunity, EvaluationMethodConfiguration} */
    private function createShelterStructure(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $root = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $shelterClass = get_class($root);
        $shelter = new $shelterClass;
        $shelter->status = Opportunity::STATUS_PHASE;
        $shelter->parent = $root;
        $shelter->name = 'Etapa de Seleção';
        $shelter->type = $root->type;
        $shelter->isOpportunityPhase = true;
        $shelter->isDataCollection = '0';
        $shelter->save(true);

        $configuration = new EvaluationMethodConfiguration;
        $configuration->opportunity = $shelter;
        $configuration->type = 'technical';
        $configuration->name = 'Etapa de Seleção';
        $configuration->save(true);

        $this->assertSame($shelter->id, $this->configurationOwnerId($configuration->id));
        $this->assertSame('0', $this->conn()->fetchOne(
            "SELECT value FROM opportunity_meta WHERE object_id = ? AND key = 'isDataCollection'",
            [$shelter->id]
        ));

        return [$root, $shelter, $configuration];
    }

    private function moveConfigurations(): void
    {
        OpportunityPhasesModule::moveEvaluationConfigurationsFromEmptyPhasesToDataCollection($this->app);
    }

    private function conn(): \Doctrine\DBAL\Connection
    {
        return $this->app->em->getConnection();
    }

    private function configurationOwnerId(int $configuration_id): int
    {
        return (int) $this->conn()->fetchOne(
            'SELECT opportunity_id FROM evaluation_method_configuration WHERE id = ?',
            [$configuration_id]
        );
    }

    private function opportunityExists(int $opportunity_id): bool
    {
        return (bool) $this->conn()->fetchOne('SELECT 1 FROM opportunity WHERE id = ?', [$opportunity_id]);
    }

    /** @return int[] */
    private function childrenIds(int $root_id): array
    {
        return array_map('intval', $this->conn()->fetchFirstColumn(
            'SELECT id FROM opportunity WHERE parent_id = ? ORDER BY id',
            [$root_id]
        ));
    }
}
