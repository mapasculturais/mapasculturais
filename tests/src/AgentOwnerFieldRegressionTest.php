<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\MetaList;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Definitions\Metadata;
use PHPUnit\Framework\Attributes\DataProvider;
use RegistrationFieldTypes\Module as RegistrationFieldTypesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\AgentBuilder;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationFieldBuilder;
use Tests\Traits\UserDirector;

/**
 * Regressão dos campos "@" (agent-owner-field) que já existiam antes dos anexos type=file.
 *
 * Contrato crítico após a correção de anexos:
 *  - rascunho: sempre sincroniza com o agente (fetchFromEntity);
 *  - inscrição enviada: campos NÃO-arquivo usam o snapshot armazenado;
 *  - inscrição enviada: anexos (type=file) continuam sincronizando com o agente;
 *  - saveToEntity grava no agente para campos simples e é no-op para anexos.
 */
class AgentOwnerFieldRegressionTest extends TestCase
{
    use UserDirector;
    use AgentDirector;
    use OpportunityBuilder;
    use RegistrationFieldBuilder;

    /** Campos escalares legados (não são type=file). */
    public static function scalarAgentFields(): array
    {
        return [
            'nomeCompleto (text)' => ['nomeCompleto', 'Fulano da Silva'],
            'emailPublico (email)' => ['emailPublico', 'fulano@example.com'],
            'passaporteNumero (text)' => ['passaporteNumero', 'AB123456'],
            'rgUF (select)' => ['rgUF', 'SP'],
            'shortDescription (textarea)' => ['shortDescription', 'Mini bio de regressão'],
        ];
    }

    /** Campos especiais (@) legados. */
    public static function specialAgentFields(): array
    {
        return [
            '@location' => ['@location'],
            '@links' => ['@links'],
            '@bankFields' => ['@bankFields'],
            '@gallery' => ['@gallery'],
            '@downloads' => ['@downloads'],
            '@videos' => ['@videos'],
        ];
    }

    public static function fileVsNonFileClassification(): array
    {
        return [
            'cpfAnexo is file' => ['cpfAnexo', true],
            'passaporteAnexo is file' => ['passaporteAnexo', true],
            'certidaoFiscalAnexo is file' => ['certidaoFiscalAnexo', true],
            'nomeCompleto is not file' => ['nomeCompleto', false],
            'emailPublico is not file' => ['emailPublico', false],
            'rgUF is not file' => ['rgUF', false],
            '@location is not file' => ['@location', false],
            '@links is not file' => ['@links', false],
            '@bankFields is not file' => ['@bankFields', false],
            '@gallery is not file' => ['@gallery', false],
            '@downloads is not file' => ['@downloads', false],
            '@videos is not file' => ['@videos', false],
        ];
    }

    private function module(): RegistrationFieldTypesModule
    {
        return App::i()->modules['RegistrationFieldTypes'];
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
            ->reset($opportunity, 'agent-owner-field', 'agent-field-' . preg_replace('/[^a-zA-Z0-9_]/', '_', $entityField))
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

    private function setAgentScalarField(Agent $agent, string $entityField, mixed $value): Agent
    {
        $agent->$entityField = $value;
        $agent->save(true);

        return $agent->refreshed();
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

    private function seedSpecialAgentField(Agent $agent, string $entityField): Agent
    {
        return match ($entityField) {
            '@location' => $this->seedLocation($agent),
            '@links' => $this->seedLinks($agent),
            '@bankFields' => $this->seedBankFields($agent),
            '@gallery' => $this->attachFileToAgent($agent, 'gallery'),
            '@downloads' => $this->attachFileToAgent($agent, 'downloads'),
            '@videos' => $this->seedVideos($agent),
            default => $agent,
        };
    }

    private function seedLocation(Agent $agent): Agent
    {
        // Formulário BR usa metadados En_*; o fetchFromEntity de @location
        // também lê address_* / endereco / publicLocation.
        $agent->En_Pais = 'BR';
        $agent->En_CEP = '01310-100';
        $agent->En_Estado = 'SP';
        $agent->En_Municipio = 'São Paulo';
        $agent->En_Nome_Logradouro = 'Av. Paulista';
        $agent->En_Num = '1000';
        $agent->endereco = 'Av. Paulista, 1000 - São Paulo/SP';
        $agent->publicLocation = true;
        $agent->save(true);

        return $agent->refreshed();
    }

    private function seedLinks(Agent $agent): Agent
    {
        $link = new MetaList();
        $link->owner = $agent;
        $link->group = 'links';
        $link->title = 'Site';
        $link->value = 'https://example.com/agente';
        $link->save(true);

        return $agent->refreshed();
    }

    private function seedVideos(Agent $agent): Agent
    {
        $video = new MetaList();
        $video->owner = $agent;
        $video->group = 'videos';
        $video->title = 'Vídeo';
        $video->value = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $video->save(true);

        return $agent->refreshed();
    }

    private function seedBankFields(Agent $agent): Agent
    {
        $agent->payment_bank_account_type = 'corrente';
        $agent->payment_bank_number = '001';
        $agent->payment_bank_branch = '1234';
        $agent->payment_bank_dv_branch = '5';
        $agent->payment_bank_account_number = '98765';
        $agent->payment_bank_dv_account_number = '4';
        $agent->save(true);

        return $agent->refreshed();
    }

    private function assertSpecialFieldValue(string $entityField, mixed $value): void
    {
        switch ($entityField) {
            case '@location':
                $this->assertIsArray($value);
                $this->assertArrayHasKey('endereco', $value);
                $this->assertArrayHasKey('address_postalCode', $value);
                $this->assertArrayHasKey('location', $value);
                $this->assertArrayHasKey('publicLocation', $value);
                $this->assertNotEmpty($value['endereco']);
                $this->assertTrue((bool) $value['publicLocation']);
                break;

            case '@links':
                $this->assertIsArray($value);
                $this->assertNotEmpty($value);
                $titles = array_map(fn ($item) => is_array($item) ? ($item['title'] ?? null) : ($item->title ?? null), $value);
                $this->assertContains('Site', $titles);
                break;

            case '@bankFields':
                $this->assertIsArray($value);
                $this->assertSame('corrente', $value['account_type']);
                $this->assertSame('001', $value['number']);
                $this->assertSame(1234, $value['branch']);
                $this->assertSame(98765, $value['account_number']);
                break;

            case '@gallery':
            case '@downloads':
                $this->assertIsArray($value);
                $this->assertNotEmpty($value);
                $this->assertArrayHasKey('name', $value[0]);
                $this->assertArrayHasKey('url', $value[0]);
                break;

            case '@videos':
                $this->assertIsArray($value);
                $this->assertNotEmpty($value);
                $first = $value[0];
                $title = is_array($first) ? ($first['title'] ?? null) : ($first->title ?? null);
                $this->assertSame('Vídeo', $title);
                break;

            default:
                $this->fail("Campo especial não mapeado no assert: {$entityField}");
        }
    }

    #[DataProvider('fileVsNonFileClassification')]
    public function testIsAgentFileEntityFieldClassification(string $entityField, bool $expected): void
    {
        $this->assertSame(
            $expected,
            $this->module()->isAgentFileEntityField($entityField),
            "Classificação incorreta de isAgentFileEntityField('{$entityField}')."
        );
    }

    #[DataProvider('scalarAgentFields')]
    public function testDraftUnserializeSyncsScalarFieldFromAgent(string $entityField, mixed $agentValue): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->setAgentScalarField($owner, $entityField, $agentValue);

        $registration = $this->createRegistrationStub($opportunity, $owner);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $value = $this->module()->unserializeAgentOwnerFieldValue(null, $registration, $metadata);

        $this->assertSame($agentValue, $value);
    }

    #[DataProvider('scalarAgentFields')]
    public function testSentUnserializeKeepsStoredSnapshotForScalarField(string $entityField, mixed $agentValue): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);

        $snapshot = is_string($agentValue) ? 'SNAPSHOT-' . $agentValue : $agentValue;
        $owner = $this->setAgentScalarField($owner, $entityField, $agentValue);

        $registration = $this->createRegistrationStub($opportunity, $owner, Registration::STATUS_SENT);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $value = $this->module()->unserializeAgentOwnerFieldValue($snapshot, $registration, $metadata);

        $this->assertSame(
            $snapshot,
            $value,
            "Campo escalar '{$entityField}' em inscrição enviada deve usar o snapshot, não o valor atual do agente."
        );
        $this->assertNotSame($agentValue, $value);
    }

    #[DataProvider('scalarAgentFields')]
    public function testSaveToEntityWritesScalarFieldOnAgent(string $entityField, mixed $agentValue): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $registration = $this->createRegistrationStub($opportunity, $owner);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $this->module()->saveToEntity($owner, $agentValue, $registration, $metadata);
        $owner->save(true);
        $owner = $owner->refreshed();

        $this->assertSame($agentValue, $owner->$entityField);
    }

    public function testSaveToEntityDoesNotWriteFileFieldAsAgentMetadata(): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField('cpfAnexo');
        $registration = $this->createRegistrationStub($opportunity, $owner);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $fake = ['id' => 999, 'name' => 'fake.pdf', 'url' => 'http://example.com/fake.pdf'];
        $this->module()->saveToEntity($owner, $fake, $registration, $metadata);
        $owner->save(true);
        $owner = $owner->refreshed();

        $this->assertEmpty(
            $owner->cpfAnexo,
            'Anexos type=file não devem ser gravados em agent_meta; vivem no FileGroup.'
        );
    }

    #[DataProvider('specialAgentFields')]
    public function testDraftUnserializeSyncsSpecialFieldFromAgent(string $entityField): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->seedSpecialAgentField($owner, $entityField);

        $registration = $this->createRegistrationStub($opportunity, $owner);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $value = $this->module()->unserializeAgentOwnerFieldValue(null, $registration, $metadata);

        $this->assertSpecialFieldValue($entityField, $value);
    }

    #[DataProvider('specialAgentFields')]
    public function testSentUnserializeKeepsStoredSnapshotForSpecialField(string $entityField): void
    {
        [$opportunity, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->seedSpecialAgentField($owner, $entityField);

        $registration = $this->createRegistrationStub($opportunity, $owner, Registration::STATUS_SENT);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $snapshot = json_encode(['snapshot' => true, 'field' => $entityField]);
        $value = $this->module()->unserializeAgentOwnerFieldValue($snapshot, $registration, $metadata);

        $this->assertEquals(
            (object) ['snapshot' => true, 'field' => $entityField],
            $value,
            "Campo especial '{$entityField}' em inscrição enviada deve usar o snapshot JSON."
        );
    }

    #[DataProvider('specialAgentFields')]
    public function testFetchFromEntityReturnsSpecialField(string $entityField): void
    {
        [, $owner, $field] = $this->createOpportunityWithAgentOwnerField($entityField);
        $owner = $this->seedSpecialAgentField($owner, $entityField);
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $value = $this->module()->fetchFromEntity($owner, null, null, $metadata);

        $this->assertSpecialFieldValue($entityField, $value);
    }

    public function testSentFileFieldStillSyncsFromAgentWhileScalarDoesNot(): void
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

        $scalarField = $this->registrationFieldBuilder
            ->reset($opportunity, 'agent-owner-field', 'agent-field-nomeCompleto')
            ->fillRequiredProperties()
            ->setEntityField('nomeCompleto')
            ->save()
            ->getInstance();

        $fileField = $this->registrationFieldBuilder
            ->reset($opportunity, 'agent-owner-field', 'agent-field-cpfAnexo')
            ->fillRequiredProperties()
            ->setEntityField('cpfAnexo')
            ->save()
            ->getInstance();

        $opportunity->registerRegistrationMetadata();

        $owner->nomeCompleto = 'Nome Atual do Agente';
        $owner->save(true);
        $owner = $this->attachFileToAgent($owner->refreshed(), 'docs-cpf');

        $registration = $this->createRegistrationStub($opportunity, $owner, Registration::STATUS_SENT);

        $scalarMeta = $this->getRegistrationMetadataDefinition($scalarField->getFieldName());
        $fileMeta = $this->getRegistrationMetadataDefinition($fileField->getFieldName());

        $scalarValue = $this->module()->unserializeAgentOwnerFieldValue('Nome no Snapshot', $registration, $scalarMeta);
        $fileValue = $this->module()->unserializeAgentOwnerFieldValue(null, $registration, $fileMeta);

        $this->assertSame('Nome no Snapshot', $scalarValue);
        $this->assertIsArray($fileValue);
        $this->assertSame('docs-cpf.png', $fileValue['name']);
    }

    public function testRegisterMetadataPropagatesFileGroupOnlyForFileFields(): void
    {
        [, , $fileField] = $this->createOpportunityWithAgentOwnerField('cpfAnexo');
        $fileMeta = $this->getRegistrationMetadataDefinition($fileField->getFieldName());
        $this->assertSame('docs-cpf', $fileMeta->config['file_group'] ?? null);
        $this->assertSame('file', $fileMeta->config['type'] ?? $fileMeta->type ?? null);

        [, , $textField] = $this->createOpportunityWithAgentOwnerField('nomeCompleto');
        $textMeta = $this->getRegistrationMetadataDefinition($textField->getFieldName());
        $this->assertArrayNotHasKey('file_group', $textMeta->config ?? []);
    }

    public function testDecodeStoredRegistrationFieldValueHandlesJsonAndPlainText(): void
    {
        $module = $this->module();

        $this->assertSame('texto simples', $module->decodeStoredRegistrationFieldValue('texto simples'));
        $this->assertSame('json string', $module->decodeStoredRegistrationFieldValue('"json string"'));
        $this->assertEquals((object) ['a' => 1], $module->decodeStoredRegistrationFieldValue('{"a":1}'));
        $this->assertSame([1, 2], $module->decodeStoredRegistrationFieldValue('[1,2]'));
        $this->assertNull($module->decodeStoredRegistrationFieldValue('null'));
        $this->assertFalse($module->decodeStoredRegistrationFieldValue('false'));
        $this->assertTrue($module->decodeStoredRegistrationFieldValue('true'));
        $this->assertSame('', $module->decodeStoredRegistrationFieldValue(null));
    }

    public function testUnserializeWithoutRegistrationReturnsStoredValue(): void
    {
        [, , $field] = $this->createOpportunityWithAgentOwnerField('nomeCompleto');
        $metadata = $this->getRegistrationMetadataDefinition($field->getFieldName());

        $value = $this->module()->unserializeAgentOwnerFieldValue('valor-sem-registration', null, $metadata);

        $this->assertSame('valor-sem-registration', $value);
    }
}
