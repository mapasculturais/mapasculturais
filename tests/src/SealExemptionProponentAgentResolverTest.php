<?php

namespace Tests;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use SealExemption\ProponentAgentResolver;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

/**
 * Decision-table tests for ProponentAgentResolver.
 *
 * Covers the actual resolver behavior (not the ideal spec) for each
 * proponent type and collective-agent mode.
 */
class SealExemptionProponentAgentResolverTest extends TestCase
{
    use AgentDirector;
    use OpportunityBuilder;
    use UserDirector;

    private ProponentAgentResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ProponentAgentResolver();
    }

    /**
     * Helper: create a base opportunity (project-owned) with the given
     * collective-agent mode on its first phase.
     */
    private function createOpportunityWithCollectiveMode(?string $mode): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $project = new Project();
        $project->name = 'Test Project';
        $project->type = 1;
        $project->owner = $owner;
        $project->save(true);

        $opportunity = $this->opportunityBuilder
            ->reset($owner, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física', 'Pessoa Jurídica', 'Coletivo', 'MEI'])
            ->getInstance();

        $opportunity->firstPhase->useAgentRelationColetivo = $mode;
        $opportunity->firstPhase->save(true);

        return $opportunity;
    }

    /**
     * Helper: create a registration with a given proponent type and owner.
     */
    private function createRegistration(Opportunity $opportunity, Agent $owner, string $proponentType): Registration
    {
        $opportunity->registerRegistrationMetadata();

        $registration = new Registration();
        $registration->opportunity = $opportunity;
        $registration->owner = $owner;
        $registration->proponentType = $proponentType;
        $registration->range = 'Test Range';
        $registration->save(true);

        return $registration;
    }

    /**
     * Helper: attach a collective agent to a registration.
     */
    private function attachCollectiveAgent(Registration $registration, Agent $collective): void
    {
        $registration->createAgentRelation($collective, 'coletivo', false, true, true);
    }

    public function testPessoaFisicaReturnsOwner(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Pessoa Física');

        $this->assertSame($owner->id, $this->resolver->resolve($registration)?->id);
    }

    public function testMeiReturnsOwner(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);

        $registration = $this->createRegistration($opportunity, $owner, 'MEI');

        $this->assertSame($owner->id, $this->resolver->resolve($registration)?->id);
    }

    public function testPessoaJuridicaWithoutCollectiveModeReturnsOwner(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode(null);
        $owner = $this->agentDirector->createAgent($opportunity->owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Pessoa Jurídica');

        $this->assertSame($owner->id, $this->resolver->resolve($registration)?->id);
    }

    public function testPessoaJuridicaWithCollectiveRequiredReturnsCollectiveAgent(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);
        $collective = $this->agentDirector->createCollective($owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Pessoa Jurídica');
        $this->attachCollectiveAgent($registration, $collective);

        $resolved = $this->resolver->resolve($registration);
        $this->assertNotNull($resolved);
        $this->assertSame($collective->id, $resolved->id);
    }

    public function testPessoaJuridicaWithCollectiveOptionalReturnsCollectiveAgent(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('optional');
        $owner = $this->agentDirector->createAgent($opportunity->owner);
        $collective = $this->agentDirector->createCollective($owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Pessoa Jurídica');
        $this->attachCollectiveAgent($registration, $collective);

        $resolved = $this->resolver->resolve($registration);
        $this->assertNotNull($resolved);
        $this->assertSame($collective->id, $resolved->id);
    }

    public function testPessoaJuridicaWithCollectiveRequiredButMissingRelationReturnsNull(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Pessoa Jurídica');

        $this->assertNull($this->resolver->resolve($registration));
    }

    public function testColetivoWithCollectiveRequiredReturnsCollectiveAgent(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);
        $collective = $this->agentDirector->createCollective($owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Coletivo');
        $this->attachCollectiveAgent($registration, $collective);

        $resolved = $this->resolver->resolve($registration);
        $this->assertNotNull($resolved);
        $this->assertSame($collective->id, $resolved->id);
    }

    public function testColetivoWithoutCollectiveModeReturnsOwner(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode(null);
        $owner = $this->agentDirector->createAgent($opportunity->owner);
        $collective = $this->agentDirector->createCollective($owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Coletivo');
        $this->attachCollectiveAgent($registration, $collective);

        $this->assertSame($owner->id, $this->resolver->resolve($registration)?->id);
    }

    public function testUnknownProponentTypeFallsBackToOwner(): void
    {
        $opportunity = $this->createOpportunityWithCollectiveMode('required');
        $owner = $this->agentDirector->createAgent($opportunity->owner);

        $registration = $this->createRegistration($opportunity, $owner, 'Tipo Desconhecido');

        $this->assertSame($owner->id, $this->resolver->resolve($registration)?->id);
    }
}
