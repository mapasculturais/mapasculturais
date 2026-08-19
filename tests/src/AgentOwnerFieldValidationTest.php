<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use RegistrationFieldTypes\Module as RegistrationFieldTypesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class AgentOwnerFieldValidationTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private const VALID_CPF = '390.533.447-05';
    private const CNPJ_AS_CPF = '04.252.011/0001-10';
    private const VALID_EMAIL = 'proponente@example.com';
    private const INVALID_EMAIL = 'nao-e-email';

    private function module(): RegistrationFieldTypesModule
    {
        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];
        return $module;
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function syncedFieldsWithRespectRules(): array
    {
        return [
            'cpf' => ['agent-owner-field', 'cpf', 'v::cpf()'],
            'cnpj' => ['agent-owner-field', 'cnpj', 'v::cnpj()'],
            'emailPublico' => ['agent-owner-field', 'emailPublico', 'v::email()'],
            'emailPrivado' => ['agent-owner-field', 'emailPrivado', 'v::email()'],
            'telefonePublico' => ['agent-owner-field', 'telefonePublico', 'v::brPhone()'],
            'site' => ['agent-owner-field', 'site', 'v::url()'],
            'space cnpj' => ['space-field', 'cnpj', 'v::cnpj()'],
            'space email' => ['space-field', 'emailPublico', 'v::email()'],
        ];
    }

    /**
     * @dataProvider syncedFieldsWithRespectRules
     */
    public function testSyncedEntityFieldValidationsIncludeRespectRules(string $field_type, string $entity_field, string $rule): void
    {
        $validations = $this->module()->getSyncedEntityFieldValidations($field_type, $entity_field);

        $this->assertArrayHasKey(
            $rule,
            $validations,
            "Campo @ {$field_type} → {$entity_field} deve herdar {$rule}"
        );
    }

    public function testBankFieldsValidationsAreCopiedForAtBankFields(): void
    {
        $validations = $this->module()->getSyncedEntityFieldValidations('agent-owner-field', '@bankFields');

        $this->assertArrayHasKey('v::attribute("account_number", null, true)', $validations);
        $this->assertArrayHasKey('v::attribute("branch", null, true)', $validations);
    }

    public function testAgentOwnerCpfMetadataReceivesCpfValidation(): void
    {
        $field_name = $this->createOwnerFieldOpportunity('cpf-agente', 'cpf');
        $registered = App::i()->getRegisteredMetadata(Registration::class);

        $this->assertArrayHasKey($field_name, $registered);
        $this->assertArrayHasKey('v::cpf()', $registered[$field_name]->config['validations'] ?? []);
    }

    public function testAgentOwnerCpfRejectsCnpjAndDoesNotPersistOnAgent(): void
    {
        $field_name = $this->createOwnerFieldOpportunity('cpf-agente', 'cpf');
        $registration = $this->newDraft($field_name);
        $owner_id = $registration->owner->id;

        $registration->$field_name = self::CNPJ_AS_CPF;

        $errors = $registration->getValidationErrors();
        $this->assertArrayHasKey($field_name, $errors, 'CNPJ em campo @ CPF deve gerar erro de validação');

        $registration->save(true);

        $agent = App::i()->repo('Agent')->find($owner_id);
        $agent_cpf = preg_replace('/\D/', '', (string) $agent->cpf);
        $posted_cnpj = preg_replace('/\D/', '', self::CNPJ_AS_CPF);

        $this->assertNotSame($posted_cnpj, $agent_cpf, 'CNPJ não pode ser persistido no CPF do agente');
    }

    public function testAgentOwnerCpfAcceptsValidCpfAndPersistsOnAgent(): void
    {
        $field_name = $this->createOwnerFieldOpportunity('cpf-agente', 'cpf');
        $registration = $this->newDraft($field_name);
        $owner_id = $registration->owner->id;

        $registration->$field_name = self::VALID_CPF;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayNotHasKey($field_name, $errors, 'CPF válido no campo @ não deve falhar');

        $registration->save(true);

        $agent = App::i()->repo('Agent')->find($owner_id);
        $this->assertSame(
            preg_replace('/\D/', '', self::VALID_CPF),
            preg_replace('/\D/', '', (string) $agent->cpf)
        );
    }

    public function testAgentOwnerEmailRejectsInvalidValue(): void
    {
        $field_name = $this->createOwnerFieldOpportunity('email-agente', 'emailPublico');
        $registration = $this->newDraft($field_name);

        $registration->$field_name = self::INVALID_EMAIL;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayHasKey($field_name, $errors);

        $registration->$field_name = self::VALID_EMAIL;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayNotHasKey($field_name, $errors);

        $registration->save(true);
        $agent = App::i()->repo('Agent')->find($registration->owner->id);
        $this->assertSame(self::VALID_EMAIL, $agent->emailPublico);
    }

    public function testNativeCpfFieldStillRejectsCnpj(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa')
                ->createField('cpf-nativo', 'cpf', title: 'CPF', required: false)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field_name = $this->opportunityBuilder->getFieldName('cpf-nativo');
        $registration = $this->registrationDirector->createDraftRegistrations($opportunity, 1)[0];
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $registration->$field_name = self::CNPJ_AS_CPF;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayHasKey($field_name, $errors, 'Campo nativo cpf deve continuar rejeitando CNPJ');
    }

    private function createOwnerFieldOpportunity(string $identifier, string $entity_field): string
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa')
                ->createOwnerField($identifier, $entity_field, title: $entity_field, required: false)
                ->done()
            ->save()
            ->refresh();

        $field_name = $this->opportunityBuilder->getFieldName($identifier);
        $this->opportunityBuilder->getInstance()->registerRegistrationMetadata();

        return $field_name;
    }

    private function newDraft(string $field_name): Registration
    {
        $opportunity = $this->opportunityBuilder->getInstance();
        /** @var Registration $registration */
        $registration = $this->registrationDirector->createDraftRegistrations($opportunity, 1)[0];
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $this->assertNotEmpty($field_name);

        return $registration;
    }
}
