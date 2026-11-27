<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\Halt;
use Opportunities\FieldsVisibleEvaluatorsPhases;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityAppealPhaseEvaluationFieldsTest extends TestCase
{
    use OpportunityBuilder, RegistrationDirector, RequestFactory, UserDirector;

    private User $owner;

    public function testEvaluatedPhaseKeepsStoredConfigurationForEveryReader(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);

        $appealValuer = $this->addValuer($appeal, 'Recurso');
        $bothCommittees = $this->addValuer($phase, 'Mérito');
        $this->addValuer($appeal, 'Recurso', $bothCommittees);

        $readers = [
            'gestor' => $this->owner,
            'avaliador do recurso' => $appealValuer,
            'avaliador das duas comissões' => $bothCommittees,
        ];

        foreach ($readers as $role => $user) {
            $this->assertEquals($stored, $this->readAs($user, $phase), "A etapa entrega ao {$role} a configuração gravada");
        }
    }

    public function testAppealPhaseInheritsParentConfigurationOnCreation(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);

        $appealValuer = $this->addValuer($appeal, 'Recurso');
        $outsider = $this->userDirector->createUser();

        $readers = [
            'gestor' => $this->owner,
            'avaliador do recurso' => $appealValuer,
            'usuário sem vínculo' => $outsider,
        ];

        foreach ($readers as $role => $user) {
            $this->assertEquals($stored, $this->readAs($user, $appeal), "O recurso nasce com a cópia da etapa para o {$role}");
        }
    }

    public function testAppealPhasePersistsOwnEditsAndIgnoresLaterParentChanges(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);
        [$appealField, $appealFile] = $this->addAppealForm($appeal);

        $this->assertEquals($stored, $this->readAs($this->owner, $appeal), 'Antes da edição o recurso mantém a cópia inicial');

        $edited = $stored;
        $edited[$appealField] = 'true';
        $edited[$appealFile] = 'true';
        $edited['agentsSummary'] = 'false';
        ksort($edited);

        $this->login($this->owner);
        $appeal = $appeal->refreshed();
        $appeal->avaliableEvaluationFields = $edited;
        $appeal->save(true);

        $parentChanged = $stored;
        $parentChanged['agentsSummary'] = 'true';
        $parentChanged['spaceSummary'] = 'true';
        $phase = $phase->refreshed();
        $phase->avaliableEvaluationFields = $parentChanged;
        $phase->save(true);

        $this->assertEquals($edited, $this->readAs($this->owner, $appeal), 'A edição no recurso persiste mesmo se o pai mudar depois');
        $this->assertEquals($edited, $this->readAs($this->addValuer($appeal, 'Recurso'), $appeal), 'O avaliador do recurso lê a configuração gravada no recurso');
    }

    public function testPhaseWithoutAppealKeepsStoredConfiguration(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $outsider = $this->userDirector->createUser();

        $this->assertEquals($stored, $this->readAs($this->owner, $phase), 'O gestor recebe a configuração gravada');
        $this->assertEquals($stored, $this->readAs($outsider, $phase), 'Qualquer leitor recebe a configuração gravada');
    }

    public function testChildPhaseThatIsNotAppealKeepsItsOwnConfiguration(): void
    {
        $phase = $this->createEvaluatedPhase();
        $this->storePartialConfiguration($phase);

        $child = $this->opportunityBuilder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->getInstance();

        $childConfiguration = ['agentsSummary' => 'false', 'spaceSummary' => 'true'];
        $child->avaliableEvaluationFields = $childConfiguration;
        $child->save(true);
        ksort($childConfiguration);

        $this->assertEquals($phase->id, $child->parent->id, 'A fase de coleta é filha da primeira fase');
        $this->assertEquals($childConfiguration, $this->readAs($this->owner, $child), 'Fase filha que não é recurso não herda a configuração do pai');
    }

    public function testAppealOfLaterPhaseInheritsThatPhaseNotTheFirstOne(): void
    {
        $this->owner = $this->userDirector->createUser();
        $this->login($this->owner);

        $builder = $this->opportunityBuilder
            ->reset(owner: $this->owner->profile, owner_entity: $this->owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $laterPhase = $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->getInstance();

        $evaluation = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $this->assertEquals($laterPhase->id, $evaluation->opportunity->id, 'A avaliação fica na fase de coleta posterior');

        $firstPhase = $laterPhase->parent;
        $firstPhase->avaliableEvaluationFields = ['agentsSummary' => 'true', 'spaceSummary' => 'true'];
        $firstPhase->save(true);

        $laterConfiguration = ['agentsSummary' => 'false', 'spaceSummary' => 'true'];
        $laterPhase = $laterPhase->refreshed();
        $laterPhase->avaliableEvaluationFields = $laterConfiguration;
        $laterPhase->save(true);
        ksort($laterConfiguration);

        $appeal = $this->createAppealPhase($laterPhase);

        $this->assertEquals($laterConfiguration, $this->readAs($this->owner, $appeal), 'O recurso copia a etapa que ele reanalisa, não a primeira fase');
    }

    public function testAppealPhaseStoresCopiedConfigurationOnCreation(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);

        $copied = $appeal->refreshed()->getStoredAvaliableEvaluationFields();
        ksort($copied);

        $this->assertEquals($stored, $copied, 'A coluna do recurso recebe a cópia inicial da etapa avaliada');
    }

    public function testAppealRegistrationCanSeeUsesAppealStoredConfiguration(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);
        [$appealField] = $this->addAppealForm($appeal);

        $liberado = null;
        $oculto = null;
        foreach ($phase->registrationFieldConfigurations as $field) {
            if ($field->title === 'Campo liberado') {
                $liberado = $field->fieldName;
            }
            if ($field->title === 'Campo oculto') {
                $oculto = $field->fieldName;
            }
        }

        $edited = $stored;
        $edited[$liberado] = 'false';
        $edited[$oculto] = 'true';
        $edited[$appealField] = 'true';
        ksort($edited);

        $this->login($this->owner);
        $appeal = $appeal->refreshed();
        $appeal->avaliableEvaluationFields = $edited;
        $appeal->save(true);

        $parentChanged = $stored;
        $parentChanged[$liberado] = 'true';
        $parentChanged[$oculto] = 'false';
        $phase = $phase->refreshed();
        $phase->avaliableEvaluationFields = $parentChanged;
        $phase->save(true);

        $appealValuer = $this->addValuer($appeal, 'Recurso');

        $this->login($this->owner);
        $appeal = $appeal->refreshed();
        $appeal->registrationFrom = new \DateTime('-1 day');
        $appeal->registrationTo = new \DateTime('+30 days');
        $appeal->save(true);

        $this->app->disableAccessControl();
        try {
            [$registration] = $this->registrationDirector->createDraftRegistrations($appeal->refreshed(), 1);
            $registrationId = $registration->id;
        } finally {
            $this->app->enableAccessControl();
        }

        foreach ([$this->owner, $appealValuer] as $reader) {
            $this->app->em->clear();
            $this->login($this->app->repo('User')->find($reader->id));
            $registration = $this->app->repo(Registration::class)->find($registrationId);

            $this->assertFalse($registration->canSee($liberado), 'No recurso o campo liberado segue a config gravada no recurso, não a do pai');
            $this->assertTrue($registration->canSee($oculto), 'No recurso o campo oculto segue a config gravada no recurso');
            $this->assertTrue($registration->canSee($appealField), 'O formulário do recurso fica visível quando marcado na config do recurso');
        }
    }

    public function testFieldsVisibleEvaluatorsIndexIncludesAppealPhase(): void
    {
        $phase = $this->createEvaluatedPhase();
        $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);
        [$appealField, $appealFile] = $this->addAppealForm($appeal);

        $get_fields = function (Opportunity $opportunity): array {
            $fields = [];
            foreach ($opportunity->registrationFieldConfigurations as $field) {
                $fields[] = $field;
            }
            foreach ($opportunity->registrationFileConfigurations as $file) {
                $fields[] = $file;
            }
            return $fields;
        };

        $index = FieldsVisibleEvaluatorsPhases::buildIndex($phase->firstPhase, $get_fields, fn () => []);

        $this->assertArrayHasKey($phase->id, $index, 'A etapa avaliada entra no índice da tela');
        $this->assertArrayHasKey($appeal->id, $index, 'A fase de recurso entra no índice mesmo fora do nextPhase');

        $appealFieldNames = [];
        foreach ($index[$appeal->id] as $item) {
            $appealFieldNames[] = $item->fieldName ?? $item->fileGroupName ?? null;
        }

        $this->assertContains($appealField, $appealFieldNames, 'O índice do recurso traz o campo do formulário do recurso');
        $this->assertContains($appealFile, $appealFieldNames, 'O índice do recurso traz o anexo do formulário do recurso');
    }

    private function createEvaluatedPhase(): Opportunity
    {
        $this->owner = $this->userDirector->createUser();
        $this->login($this->owner);

        $builder = $this->opportunityBuilder
            ->reset(owner: $this->owner->profile, owner_entity: $this->owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa única')
                ->createField('liberado', 'text', title: 'Campo liberado')
                ->createField('oculto', 'text', title: 'Campo oculto')
                ->done()
            ->save();

        $evaluation = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $phase = $evaluation->opportunity->refreshed();
        $this->assertCount(2, $phase->registrationFieldConfigurations, 'A avaliação fica na fase dona dos campos');

        return $phase;
    }

    private function storePartialConfiguration(Opportunity $phase): array
    {
        $attachment = new RegistrationFileConfiguration();
        $attachment->owner = $phase;
        $attachment->step = $phase->registrationFieldConfigurations[0]->step;
        $attachment->title = 'Anexo oculto';
        $attachment->save(true);

        $phase = $phase->refreshed();

        $configuration = ['agentsSummary' => 'true'];
        foreach ($phase->registrationFieldConfigurations as $field) {
            $configuration[$field->fieldName] = $field->title === 'Campo liberado' ? 'true' : 'false';
        }
        foreach ($phase->registrationFileConfigurations as $file) {
            $configuration[$file->fileGroupName] = 'false';
        }

        $phase->avaliableEvaluationFields = $configuration;
        $phase->save(true);

        ksort($configuration);
        return $configuration;
    }

    private function createAppealPhase(Opportunity $parent): Opportunity
    {
        $this->login($this->owner);
        $this->app->request = $this->requestFactory->mapasPOST('opportunity', 'createAppealPhase', [$parent->id], ['id' => $parent->id]);
        $this->app->response = new Response();
        $controller = $this->app->controller('opportunity');
        $controller->setRequestData(['id' => $parent->id]);

        try {
            $controller->callAction('POST', 'createAppealPhase', []);
        } catch (Halt) {
        }

        $this->assertInstanceOf(Opportunity::class, $parent->appealPhase);
        return $parent->appealPhase;
    }

    private function addAppealForm(Opportunity $appeal): array
    {
        $step = $this->app->repo(RegistrationStep::class)->findOneBy(['opportunity' => $appeal]);

        $field = new RegistrationFieldConfiguration();
        $field->owner = $appeal;
        $field->step = $step;
        $field->fieldType = 'textarea';
        $field->title = 'Justificativa do recurso';
        $field->save(true);

        $file = new RegistrationFileConfiguration();
        $file->owner = $appeal;
        $file->step = $step;
        $file->title = 'Comprovação extra';
        $file->save(true);

        return [$field->fieldName, $file->fileGroupName];
    }

    private function addValuer(Opportunity $phase, string $committee, ?User $valuer = null): User
    {
        $valuer ??= $this->userDirector->createUser();
        $this->login($this->owner);

        $this->app->disableAccessControl();
        try {
            $phase->refreshed()->evaluationMethodConfiguration->createAgentRelation($valuer->profile->refreshed(), $committee, true);
        } finally {
            $this->app->enableAccessControl();
        }

        return $valuer;
    }

    private function readAs(User $reader, Opportunity $phase): array
    {
        $phaseId = $phase->id;
        $readerId = $reader->id;

        $this->app->em->clear();
        $this->login($this->app->repo('User')->find($readerId));

        $value = (array) $this->app->repo('Opportunity')->find($phaseId)->avaliableEvaluationFields;
        ksort($value);

        return $value;
    }
}
