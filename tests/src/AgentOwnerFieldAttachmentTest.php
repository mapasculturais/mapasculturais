<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\AgentFile;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Definitions\Metadata;
use PHPUnit\Framework\Attributes\DataProvider;
use RegistrationFieldTypes\Module as RegistrationFieldTypesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\AgentBuilder;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RegistrationFieldBuilder;
use Tests\Traits\UserDirector;

/**
 * Integração: campos "@" de anexo (type=file) no formulário de inscrição
 * devem carregar o arquivo já existente no FileGroup do agente (spec-ed8988d8).
 */
class AgentOwnerFieldAttachmentTest extends TestCase
{
    use UserDirector;
    use AgentDirector;
    use OpportunityBuilder;
    use RegistrationDirector;
    use RegistrationFieldBuilder;

    public static function agentAttachmentFields(): array
    {
        return [
            'passaporteAnexo' => ['passaporteAnexo', 'docs-passaporte'],
            'racaAnexo' => ['racaAnexo', 'docs-raca'],
            'cpfAnexo (legado)' => ['cpfAnexo', 'docs-cpf'],
        ];
    }

    private function attachFileToAgent(Agent $agent, string $fileGroup): Agent
    {
        $builder = new AgentBuilder();
        $builder->reset($agent->user);
        $reflection = new \ReflectionClass($builder);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue($builder, $agent);
        $builder->addFileGroup($fileGroup);

        return $agent->refreshed();
    }

    private function createOpportunityWithAgentOwnerField(string $entityField): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner, owner_entity: $owner)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save()
            ->firstPhase()
                ->fillRequiredProperties()
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field = $this->registrationFieldBuilder
            ->reset($opportunity, 'agent-owner-field', 'agent-field-' . $entityField)
            ->fillRequiredProperties()
            ->setEntityField($entityField)
            ->save()
            ->getInstance();

        $opportunity->registerRegistrationMetadata();

        return [$opportunity, $owner, $field];
    }

    private function createRegistrationStub(Opportunity $opportunity, Agent $owner, ?int $status = null): Registration
    {
        $registration = new Registration();
        $registration->opportunity = $opportunity;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';

        if ($status !== null) {
            $property = new \ReflectionProperty(Registration::class, 'status');
            $property->setAccessible(true);
            $property->setValue($registration, $status);
        }

        return $registration;
    }

    private function getRegistrationMetadataDefinition(string $fieldName): Metadata
    {
        $registered = App::i()->getRegisteredMetadata(Registration::class);

        return $registered[$fieldName];
    }

    #[DataProvider('agentAttachmentFields')]
    public function testFetchFromEntityReturnsAgentAttachment(string $entityField, string $fileGroup): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        [, $owner] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->attachFileToAgent($owner, $fileGroup);

        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];

        $result = $module->fetchAgentFileFieldFromEntity($owner, $entityField);

        $this->assertNotNull($result);
        $this->assertSame($fileGroup . '.png', $result['name']);
        $this->assertNotEmpty($result['url']);
    }

    #[DataProvider('agentAttachmentFields')]
    public function testUnserializeLoadsAttachmentOnDraftRegistration(string $entityField, string $fileGroup): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->attachFileToAgent($owner, $fileGroup);

        $registration = $this->createRegistrationStub($opportunity, $owner);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];
        $value = $module->unserializeAgentOwnerFieldValue(null, $registration, $metadata);

        $this->assertIsArray($value);
        $this->assertSame($fileGroup . '.png', $value['name']);
        $this->assertNotEmpty($value['url']);
    }

    #[DataProvider('agentAttachmentFields')]
    public function testUnserializeLoadsAttachmentOnSentRegistration(string $entityField, string $fileGroup): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->attachFileToAgent($owner, $fileGroup);

        $registration = $this->createRegistrationStub($opportunity, $owner, Registration::STATUS_SENT);

        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];
        $value = $module->unserializeAgentOwnerFieldValue(null, $registration, $metadata);

        $this->assertIsArray($value);
        $this->assertSame($fileGroup . '.png', $value['name']);
    }

    public function testPartialFileTemplateExists(): void
    {
        $path = dirname(__DIR__) . '/src/modules/RegistrationFieldTypes/layouts/parts/registration-field-types/fields/file.php';
        $this->assertFileExists($path);
    }

    public function testUserWithRegistrationViewCanDownloadReferencedAgentFile(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        [$opportunity, , $field] = $this->createOpportunityWithAgentOwnerField('passaporteAnexo');

        $step = new RegistrationStep();
        $step->setOpportunity($opportunity);
        $step->name = 'Etapa 1';
        $step->save(true);
        $field->step = $step;
        $field->save(true);
        $opportunity->registerRegistrationMetadata();

        $proponent = $this->userDirector->createUser([]);
        $proponent_agent = $this->agentDirector->createAgent($proponent->profile);
        $proponent_agent = $this->attachFileToAgent($proponent_agent, 'docs-passaporte');

        /** @var AgentFile $file */
        $file = $proponent_agent->getFile('docs-passaporte');
        $this->assertNotNull($file);

        $registration = $this->registrationDirector->createSentRegistrationForAgent($opportunity, $proponent_agent);

        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];
        $registration->{$field->fieldName} = json_encode(
            $module->fetchAgentFileFieldFromEntity($proponent_agent, 'passaporteAnexo')
        );
        $app = App::i();
        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();
        $registration = $registration->refreshed();

        $stranger = $this->userDirector->createUser([]);
        $this->login($stranger);
        $this->assertFalse($file->canUser('view', $stranger));

        $this->login($admin);
        $this->assertTrue($registration->canUser('view', $admin));
        $this->assertTrue($file->canUser('view', $admin));
    }
}
