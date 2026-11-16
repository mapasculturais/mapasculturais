<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityAppealPhaseEvaluationFieldsTest extends TestCase
{
    use OpportunityBuilder, RequestFactory, UserDirector;

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

    public function testAppealPhaseFollowsEvaluatedPhaseAndShowsItsOwnForm(): void
    {
        $phase = $this->createEvaluatedPhase();
        $stored = $this->storePartialConfiguration($phase);
        $appeal = $this->createAppealPhase($phase);
        [$appealField, $appealFile] = $this->addAppealForm($appeal);

        $appealValuer = $this->addValuer($appeal, 'Recurso');
        $outsider = $this->userDirector->createUser();

        $expected = $stored + [$appealField => 'true', $appealFile => 'true'];
        ksort($expected);

        $readers = [
            'gestor' => $this->owner,
            'avaliador do recurso' => $appealValuer,
            'usuário sem vínculo' => $outsider,
        ];

        foreach ($readers as $role => $user) {
            $this->assertEquals($expected, $this->readAs($user, $appeal), "O recurso entrega ao {$role} a configuração da etapa e o próprio formulário");
        }
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

    public function testAppealOfLaterPhaseFollowsThatPhaseNotTheFirstOne(): void
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

        $this->assertEquals($laterConfiguration, $this->readAs($this->owner, $appeal), 'O recurso segue a etapa que ele reanalisa, não a primeira fase');
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
