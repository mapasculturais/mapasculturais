<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\TestCase;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Concessao parcial de selos apos avaliacao manual com status 3 (Nao selecionada)
 * e 8 (Suplente).
 *
 * Regra de negocio (spec-b9e4a024.md §3.1):
 * - Documental + status 3/8: parcial — so selos com 100% dos invalidadores validos.
 * - Documental + status 10: concede TODOS os selos sem validacao campo-a-campo.
 * - Nao-documental + status 3/8: nenhum selo.
 * - Tecnica + qualquer status: nenhum selo.
 * - Status 0/1/2: nenhum selo.
 *
 * Selo sem invalidadores: sempre concedido (quando status permite e ha avaliacao).
 */
class SealPartialGrantTest extends TestCase
{
    use AgentDirector;
    use OpportunityBuilder;
    use SealDirector;
    use UserDirector;

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Cria um selo com a lockedFieldsConfig informada.
     *
     * @param Agent $owner Agente dono do selo (geralmente o admin).
     * @param array $lockedFieldsConfig Ex.: ['agent.name' => ['hasExpiry'=>true,'periodValue'=>1,'periodUnit'=>'year','isInvalidator'=>true]]
     */
    private function createSealWithConfig(Agent $owner, array $lockedFieldsConfig = []): Seal
    {
        $seal = $this->sealDirector->createSeal($owner);
        $seal->validPeriod = 0;
        $seal->lockedFieldsConfig = $lockedFieldsConfig;
        $seal->save(true);

        return $seal;
    }

    /**
     * Cria um selo SEM campos invalidadores.
     * Equivalente a lockedFieldsConfig vazia.
     */
    private function createSealWithoutInvalidators(Agent $owner): Seal
    {
        return $this->createSealWithConfig($owner, []);
    }

    /**
     * Cria uma oportunidade com primeira fase documental, config de selos e
     * campos de inscricao (agent-owner-field) para cada entityField informado.
     *
     * @param Seal[] $seals
     * @param string[] $agentEntityFields Ex.: ['name', 'shortDescription']
     * @return array{0: Opportunity, 1: array<string, RegistrationFieldConfiguration>}
     *     Opportunity = primeira fase; fieldConfigs = entityField => RegistrationFieldConfiguration
     */
    private function createDocumentaryOpportunityWithSeals(array $seals, array $agentEntityFields = []): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $ownerAgent = $admin->profile;

        $project = new Project();
        $project->name = 'Test Project Partial Grant';
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

        $sealIds = array_map(fn(Seal $s) => $s->id, $seals);
        $emc->sealExemptionConfig = (object) ['seals' => $sealIds];
        $emc->save(true);

        // Criar RegistrationStep para suportar os campos do formulário
        $step = new RegistrationStep();
        $step->setOpportunity($firstPhase);
        $step->name = 'Dados do proponente';
        $step->displayOrder = 0;
        $step->save(true);

        $fieldConfigs = [];
        foreach ($agentEntityFields as $entityField) {
            $field = new RegistrationFieldConfiguration();
            $field->owner = $firstPhase;
            $field->step = $step;
            $field->fieldType = 'agent-owner-field';
            $field->title = 'Field for ' . $entityField;
            $field->displayOrder = 0;
            $field->config = ['entityField' => $entityField];
            $field->save(true);

            $fieldConfigs[$entityField] = $field;
        }

        $firstPhase->registerRegistrationMetadata();

        /** @var Opportunity $firstPhase */
        return [$firstPhase, $fieldConfigs];
    }

    /**
     * Cria uma inscricao na fase de avaliacao com previousPhaseRegistrationId
     * para que o hook guard considere uma entrada de fase.
     */
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
     * Cria e envia uma avaliacao documental com evaluationData definindo quais
     * campos foram marcados como 'valid' ou 'invalid'.
     *
     * O status e definido via Reflection para evitar o hook
     * entity(RegistrationEvaluation).setStatus(<<*>>) que chama
     * $em->getUserRelation($user)->updateSummary() e falha quando a EMC
     * nao tem relacao de avaliador para o usuario atual.
     *
     * @param Registration $registration
     * @param array $fieldEvaluations Format: [fieldConfigId => 'valid'|'invalid', ...]
     */
    private function createSentDocumentaryEvaluation(
        Registration $registration,
        array $fieldEvaluations
    ): void {
        $app = App::i();

        $evaluation = new RegistrationEvaluation();
        $evaluation->registration = $registration->refreshed();
        $evaluation->user = $app->user;

        $evaluationData = [];
        foreach ($fieldEvaluations as $fieldId => $result) {
            $evaluationData[$fieldId] = [
                'fieldId' => (string) $fieldId,
                'evaluation' => $result,
                'label' => 'Field ' . $fieldId,
            ];
        }

        $app->disableAccessControl();
        try {
            $evaluation->setEvaluationData($evaluationData);

            // Bypass MagicSetter -> Entity::setStatus -> hook that calls
            // getUserRelation()->updateSummary() (null when no valuer relation).
            $ref = new \ReflectionProperty(RegistrationEvaluation::class, 'status');
            $ref->setAccessible(true);
            $ref->setValue($evaluation, RegistrationEvaluation::STATUS_SENT);

            $evaluation->save(true);
        } finally {
            $app->enableAccessControl();
        }
    }

    /**
     * Define o status da inscricao com bypass de access control.
     */
    private function setRegistrationStatus(Registration $registration, string $method, bool $flush = true): void
    {
        $app = App::i();
        $app->disableAccessControl();
        try {
            $registration->$method($flush);
        } finally {
            $app->enableAccessControl();
        }
    }

    /**
     * Retorna os IDs de selos concedidos ao agente.
     *
     * @return int[]
     */
    private function getGrantedSealIds(Agent $agent): array
    {
        $agent = $agent->refreshed();
        $ids = [];
        foreach ($agent->getSealRelations() as $relation) {
            $ids[] = $relation->seal->id;
        }
        return $ids;
    }

    // ============================================================
    // GRUPO A — Status 3 (Nao selecionada) com avaliacao documental
    // ============================================================

    /**
     * M04: 2 selos, selo A com invalidador validado, selo B com invalidador
     * invalidado. Status 3 → so selo A concedido.
     */
    public function testStatusNotApprovedGrantsOnlySealsWithAllInvalidatorsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($sealA->id, $granted, 'Selo A (invalidador validado) deve ser concedido.');
        $this->assertNotContains($sealB->id, $granted, 'Selo B (invalidador invalidado) NAO deve ser concedido.');
    }

    /**
     * M07: 2 selos, todos os invalidadores validados. Status 3 → ambos concedidos.
     */
    public function testStatusNotApprovedGrantsAllSealsWhenAllInvalidatorsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($sealA->id, $granted, 'Selo A deve ser concedido.');
        $this->assertContains($sealB->id, $granted, 'Selo B deve ser concedido.');
    }

    /**
     * M08: selo A com nenhum invalidador validado, selo B com todos validados.
     * Status 3 → so selo B.
     */
    public function testStatusNotApprovedGrantsOnlyFullyValidSealWhenOtherHasInvalidField(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertNotContains($sealA->id, $granted, 'Selo A (invalidador invalido) NAO deve ser concedido.');
        $this->assertContains($sealB->id, $granted, 'Selo B (todos validados) deve ser concedido.');
    }

    /**
     * M09: 2 selos, ambos com invalidadores invalidados. Status 3 → nenhum.
     */
    public function testStatusNotApprovedGrantsNoSealWhenAllHaveInvalidFields(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Nenhum selo deve ser concedido quando todos invalidadores estao invalidos.');
    }

    // ============================================================
    // GRUPO B — Status 8 (Suplente) com avaliacao documental
    // ============================================================

    /**
     * Espelho do Grupo A para status 8.
     */
    public function testStatusWaitlistGrantsOnlySealsWithAllInvalidatorsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($sealA->id, $granted, 'Selo A (invalidador validado) deve ser concedido.');
        $this->assertNotContains($sealB->id, $granted, 'Selo B (invalidador invalidado) NAO deve ser concedido.');
    }

    public function testStatusWaitlistGrantsAllSealsWhenAllInvalidatorsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($sealA->id, $granted);
        $this->assertContains($sealB->id, $granted);
    }

    public function testStatusWaitlistGrantsOnlyFullyValidSealWhenOtherHasInvalidField(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertNotContains($sealA->id, $granted);
        $this->assertContains($sealB->id, $granted);
    }

    public function testStatusWaitlistGrantsNoSealWhenAllHaveInvalidFields(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted);
    }

    // ============================================================
    // GRUPO C — Selo sem invalidadores
    // ============================================================

    /**
     * C01: Selo com lockedFieldsConfig vazia → sempre concedido em status 3.
     */
    public function testStatusNotApprovedGrantsSealWithoutAnyLockedFields(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithoutInvalidators($admin->profile);

        // Cria um campo para que $evaluatedFields seja nao-vazio (fail-safe do servico).
        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($seal->id, $granted, 'Selo sem lockedFields deve ser sempre concedido.');
    }

    /**
     * C02: Selo com campos mas nenhum isInvalidator=true → concedido.
     */
    public function testStatusNotApprovedGrantsSealWithOnlyNonInvalidatorFields(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($seal->id, $granted, 'Selo sem invalidadores deve ser concedido.');
    }

    /**
     * C03: Selo com invalidador validado + nao-invalidador invalidado → concedido.
     * O nao-invalidador nao bloqueia a concessao.
     */
    public function testStatusNotApprovedGrantsSealWhenNonInvalidatorFieldExpiredButInvalidatorValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains(
            $seal->id,
            $granted,
            'Selo com invalidador valido deve ser concedido mesmo se nao-invalidador esta invalido.'
        );
    }

    /**
     * C04: Selo com invalidador invalidado → nao concedido.
     */
    public function testStatusNotApprovedDoesNotGrantSealWhenInvalidatorFieldInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertNotContains($seal->id, $granted, 'Selo com invalidador invalido NAO deve ser concedido.');
    }

    /**
     * C05: Selo com campo hasExpiry=false (sem chave isInvalidator) → concedido.
     * Campos sem expiracao nunca sao invalidadores.
     */
    public function testStatusNotApprovedGrantsSealWithFieldWithoutExpiry(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => false],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains(
            $seal->id,
            $granted,
            'Selo cujo unico campo tem hasExpiry=false (nao-invalidador) deve ser concedido.'
        );
    }

    // ============================================================
    // GRUPO D — Idempotencia
    // ============================================================

    /**
     * Executar setStatusToNotApproved duas vezes → nao duplica relacoes de selo.
     */
    public function testStatusNotApprovalIsIdempotentDoesNotDuplicateSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $countAfterFirst = count($this->getGrantedSealIds($owner));

        // Segunda execucao do mesmo hook.
        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $countAfterSecond = count($this->getGrantedSealIds($owner));

        $this->assertSame(
            $countAfterFirst,
            $countAfterSecond,
            'Segunda execucao do hook status(notapproved) nao deve duplicar selos.'
        );
        $this->assertSame(1, $countAfterSecond, 'Deve haver exatamente 1 relacao de selo apos duas execucoes.');
    }

    /**
     * Executar setStatusToWaitlist duas vezes → nao duplica relacoes de selo.
     */
    public function testStatusWaitlistIsIdempotentDoesNotDuplicateSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $countAfterFirst = count($this->getGrantedSealIds($owner));

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $countAfterSecond = count($this->getGrantedSealIds($owner));

        $this->assertSame($countAfterFirst, $countAfterSecond);
        $this->assertSame(1, $countAfterSecond, 'Deve haver exatamente 1 relacao de selo apos duas execucoes.');
    }

    /**
     * Status 3 (concede parcial) → draft (limpa flags) → status 10 (concede todos).
     * Selos ja concedidos pelo parcial nao devem ser duplicados.
     */
    public function testDraftResetClearsFlagsAndReApprovalGrantsAgain(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        // Status 3 → concede selo parcialmente.
        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');
        $this->assertSame(1, count($this->getGrantedSealIds($owner)));

        // Volta a rascunho (limpa flags de isencao automatica).
        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToDraft');

        // Status 10 → concede TODOS (o selo ja concedido nao deve ser duplicado).
        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame(1, count($granted), 'Apos re-aprovacao, nao deve duplicar selo ja concedido.');
        $this->assertContains($seal->id, $granted);
    }

    // ============================================================
    // GRUPO E — Status que nao concedem selos
    // ============================================================

    /**
     * Status 0 (Rascunho) → nenhum selo.
     */
    public function testStatusDraftGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToDraft');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Status 0 (Rascunho) nao deve conceder selos.');
    }

    /**
     * Status 1 (Pendente/Sent) → nenhum selo.
     */
    public function testStatusSentGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToSent');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Status 1 (Pendente) nao deve conceder selos.');
    }

    /**
     * Status 2 (Invalida) → concessao parcial (igual status 3/8).
     * Selo com invalidador validado → concedido.
     */
    public function testStatusInvalidGrantsSealsWithAllInvalidatorsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToInvalid');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($seal->id, $granted, 'Status 2 (Invalida) deve conceder selos cujos invalidadores estao validados.');
    }

    /**
     * Status 2 (Invalida) → concessao parcial.
     * Selo com invalidador invalidado → nao concedido.
     */
    public function testStatusInvalidDoesNotGrantSealsWhenInvalidatorInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToInvalid');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Status 2 (Invalida) nao deve conceder selo cujo invalidador foi invalidado.');
    }

    // ============================================================
    // GRUPO F — Metodo nao-documental e tecnica em status 3/8
    // ============================================================

    /**
     * E03: Metodo simple + status 3 → nenhum selo (decisao Questao 1: conceder nenhum).
     * Metodos nao-documentais nao produzem dados campo-a-campo; sem fundamento
     * para concessao parcial.
     */
    public function testStatusNotApprovedWithSimpleEvaluationGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->sealDirector->createSeal($admin->profile);

        // Oportunidade com avaliacao simple (nao-documental).
        $project = new Project();
        $project->name = 'Test Project Simple';
        $project->type = 1;
        $project->owner = $admin->profile;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($admin->profile, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $emc->sealExemptionConfig = (object) ['seals' => [$seal->id]];
        $emc->save(true);

        $opportunity = $emc->opportunity;

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Metodo simple + status 3 nao deve conceder selos.');
    }

    /**
     * E04: Metodo simple + status 8 → nenhum selo.
     */
    public function testStatusWaitlistWithSimpleEvaluationGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->sealDirector->createSeal($admin->profile);

        $project = new Project();
        $project->name = 'Test Project Simple Waitlist';
        $project->type = 1;
        $project->owner = $admin->profile;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($admin->profile, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $emc->sealExemptionConfig = (object) ['seals' => [$seal->id]];
        $emc->save(true);

        $opportunity = $emc->opportunity;

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Metodo simple + status 8 nao deve conceder selos.');
    }

    /**
     * E06: Metodo technical + status 3 → nenhum selo.
     * Avaliacao tecnica nunca concede selos (restricao §3.1).
     *
     * Nota: a configuracao sealExemptionConfig nao pode ser ativada em EMC
     * tecnica (guarda em Opportunities/Module.php impede). Sem config ativa,
     * o servico retorna false antes mesmo de avaliar o tipo. O teste garante
     * que nenhum selo e concedido neste cenario.
     */
    public function testStatusNotApprovedWithTechnicalEvaluationGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $project = new Project();
        $project->name = 'Test Project Technical';
        $project->type = 1;
        $project->owner = $admin->profile;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($admin->profile, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::technical)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $opportunity = $emc->opportunity;

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Metodo technical + status 3 nao deve conceder selos.');
    }

    /**
     * E07: Metodo technical + status 8 → nenhum selo.
     */
    public function testStatusWaitlistWithTechnicalEvaluationGrantsNoSeals(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $project = new Project();
        $project->name = 'Test Project Technical Waitlist';
        $project->type = 1;
        $project->owner = $admin->profile;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($admin->profile, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::technical)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $opportunity = $emc->opportunity;

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToWaitlist');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame([], $granted, 'Metodo technical + status 8 nao deve conceder selos.');
    }

    // ============================================================
    // GRUPO G — Regressao: status 10 inalterado
    // ============================================================

    /**
     * R01: Status 10 com avaliacao documental onde TODOS os campos estao validados
     * → TODOS os selos concedidos.
     *
     * Para status 10 com avaliacao documental, o servico mantem o comportamento
     * atual: canGrantValidatorSealsAfterSelection() → allConfiguredSealFieldsAreValid()
     * exige que todos os campos estejam validados. E necessario criar uma
     * avaliacao com todos os campos marcados como 'valid'.
     *
     * Este teste garante que o fluxo de aprovacao (status 10) nao foi quebrado
     * pela implementacao da concessao parcial.
     */
    public function testStatusApprovedGrantsAllSealsWithValidEvaluation(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        // Avaliacao com TODOS os campos validos — pre-requisito para status 10
        // em avaliacao documental (allConfiguredSealFieldsAreValid).
        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'valid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains($sealA->id, $granted, 'Status 10 deve conceder selo A quando todos campos validados.');
        $this->assertContains($sealB->id, $granted, 'Status 10 deve conceder selo B quando todos campos validados.');
    }

    /**
     * R02: Status 10 com campos invalidados → NENHUM selo concedido.
     *
     * Para status 10 com avaliacao documental, allConfiguredSealFieldsAreValid()
     * retorna false quando algum campo esta invalido. Este comportamento e
     * mantido pela implementacao da concessao parcial — status 10 NAO faz
     * concessao parcial, apenas status 2/3/8.
     *
     * Este teste garante que a concessao parcial nao vazou para o status 10.
     */
    public function testStatusApprovedDoesNotGrantSealsWithInvalidFields(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $sealA = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);
        $sealB = $this->createSealWithConfig($admin->profile, [
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$sealA, $sealB],
            ['name', 'shortDescription']
        );

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        // Avaliacao com TODOS os campos invalidos.
        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'invalid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame(
            [],
            $granted,
            'Status 10 com campos invalidados NAO deve conceder selos — comportamento mantido, parcialidade nao vazou para status 10.'
        );
    }

    // ============================================================
    // GRUPO H — Conditions na concessao pos-avaliacao
    // ============================================================

    /**
     * Status 10: invalidador com condition nao satisfeita (relevado) nao bloqueia
     * a concessao quando os demais campos do selo estao validos.
     *
     * Replica o caso PE: cota = Nao → Raça/cor nao e exigida.
     */
    public function testStatusApprovedGrantsWhenConditionalInvalidatorIsWaived(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name', 'shortDescription']
        );

        $emc = $opportunity->evaluationMethodConfiguration;
        $config = (array) $emc->sealExemptionConfig;
        $config['conditions'] = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];
        $emc->sealExemptionConfig = (object) $config;
        $emc->save(true);

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        // Condicao nao satisfeita → agent.name relevado
        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false;
        $registration->save(true);
        $app->enableAccessControl();

        // name nao avaliado / invalid; shortDescription valid — so o segundo e exigido
        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains(
            $seal->id,
            $granted,
            'Status 10 deve conceder o selo quando o invalidador condicional esta relevado e os demais campos sao validos.'
        );
    }

    /**
     * Status 10: condition satisfeita → invalidador continua exigido.
     */
    public function testStatusApprovedDoesNotGrantWhenConditionalInvalidatorAppliesAndIsInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name', 'shortDescription']
        );

        $emc = $opportunity->evaluationMethodConfiguration;
        $config = (array) $emc->sealExemptionConfig;
        $config['conditions'] = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];
        $emc->sealExemptionConfig = (object) $config;
        $emc->save(true);

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = '1';
        $registration->save(true);
        $app->enableAccessControl();

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['name']->id => 'invalid',
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertSame(
            [],
            $granted,
            'Status 10 NAO deve conceder quando a condition esta satisfeita e o invalidador ficou invalido.'
        );
    }

    /**
     * Status 3: mesma relevacao aplica-se na concessao parcial.
     */
    public function testStatusNotApprovedGrantsWhenConditionalInvalidatorIsWaived(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $seal = $this->createSealWithConfig($admin->profile, [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.shortDescription' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        [$opportunity, $fieldConfigs] = $this->createDocumentaryOpportunityWithSeals(
            [$seal],
            ['name', 'shortDescription']
        );

        $emc = $opportunity->evaluationMethodConfiguration;
        $config = (array) $emc->sealExemptionConfig;
        $config['conditions'] = [
            (string) $seal->id => [
                'agent.name' => [
                    'clauses' => [
                        ['field' => 'appliedForQuota', 'values' => ['1']],
                    ],
                ],
            ],
        ];
        $emc->sealExemptionConfig = (object) $config;
        $emc->save(true);

        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $app = App::i();
        $app->disableAccessControl();
        $registration->appliedForQuota = false;
        $registration->save(true);
        $app->enableAccessControl();

        $this->createSentDocumentaryEvaluation($registration, [
            $fieldConfigs['shortDescription']->id => 'valid',
        ]);

        $this->setRegistrationStatus($registration->refreshed(), 'setStatusToNotApproved');

        $granted = $this->getGrantedSealIds($owner);

        $this->assertContains(
            $seal->id,
            $granted,
            'Status 3 deve conceder o selo quando o invalidador condicional esta relevado e os demais sao validos.'
        );
    }
}
