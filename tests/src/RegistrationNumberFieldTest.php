<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use RegistrationFieldTypes\Module as RegistrationFieldTypesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class RegistrationNumberFieldTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use AgentDirector;
    use UserDirector;
    use RequestFactory;

    private function module(): RegistrationFieldTypesModule
    {
        /** @var RegistrationFieldTypesModule $module */
        $module = App::i()->modules['RegistrationFieldTypes'];
        return $module;
    }

    private function makeNumberField(array $config = []): RegistrationFieldConfiguration
    {
        $field = new RegistrationFieldConfiguration();
        $field->fieldType = 'number';
        $field->title = 'Campo número';
        $field->required = true;
        $field->config = $config;
        return $field;
    }

    public function testCountNumericDigitsUsesSavedRepresentation(): void
    {
        $module = $this->module();

        $this->assertSame(1, $module->countNumericDigits(0));
        $this->assertSame(1, $module->countNumericDigits('0'));
        $this->assertSame(6, $module->countNumericDigits(963010));
        $this->assertSame(6, $module->countNumericDigits('000963010')); // numérico → 963010
        $this->assertSame(0, $module->countNumericDigits(null));
        $this->assertSame(0, $module->countNumericDigits(''));
    }

    public function testAllowZeroDefaultsToTrueForLegacy(): void
    {
        $module = $this->module();
        $field = $this->makeNumberField([]);

        $this->assertSame([], $module->validateNumberFieldValue($field, 0));
        $this->assertSame([], $module->validateNumberFieldValue($field, '0'));
    }

    public function testRejectZeroWhenConfigured(): void
    {
        $module = $this->module();
        $field = $this->makeNumberField(['allowZero' => false]);

        $errors = $module->validateNumberFieldValue($field, 0);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('zero', mb_strtolower($errors[0]));

        $this->assertSame([], $module->validateNumberFieldValue($field, 963010));
        $this->assertSame([], $module->validateNumberFieldValue($field, null));
        $this->assertSame([], $module->validateNumberFieldValue($field, ''));
    }

    public function testMinMaxDigitsOnlyWhenConfigured(): void
    {
        $module = $this->module();

        $free = $this->makeNumberField([]);
        $this->assertSame([], $module->validateNumberFieldValue($free, 12));

        $minOnly = $this->makeNumberField(['minDigits' => 5]);
        $this->assertNotEmpty($module->validateNumberFieldValue($minOnly, 1234));
        $this->assertSame([], $module->validateNumberFieldValue($minOnly, 12345));

        $maxOnly = $this->makeNumberField(['maxDigits' => 4]);
        $this->assertNotEmpty($module->validateNumberFieldValue($maxOnly, 12345));
        $this->assertSame([], $module->validateNumberFieldValue($maxOnly, 1234));

        $both = $this->makeNumberField(['minDigits' => 3, 'maxDigits' => 5]);
        $this->assertNotEmpty($module->validateNumberFieldValue($both, 12));
        $this->assertSame([], $module->validateNumberFieldValue($both, 1234));
        $this->assertNotEmpty($module->validateNumberFieldValue($both, 123456));
    }

    public function testSetEntityPropertiesDoesNotCastEmptyToZero(): void
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
                ->createField('conta', 'number', title: 'Conta', required: true, config: ['allowZero' => false])
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field_name = $this->opportunityBuilder->getFieldName('conta');
        $registration = $this->registrationDirector->createDraftRegistrations($opportunity, 1)[0];

        $controller = App::i()->controller('registration');
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $registration->$field_name = 963010;
        $registration->save(true);

        $controller->setEntityProperties($registration, [$field_name => '']);
        $this->assertNull($registration->$field_name, 'String vazia não deve virar 0 no cast');

        $controller->setEntityProperties($registration, [$field_name => null]);
        $this->assertNull($registration->$field_name, 'null não deve virar 0 no cast');

        $controller->setEntityProperties($registration, [$field_name => '0']);
        $this->assertSame(0.0, (float) $registration->$field_name, 'Zero explícito continua 0');

        $controller->setEntityProperties($registration, [$field_name => '000963010']);
        $this->assertSame(963010.0, (float) $registration->$field_name);
    }

    public function testSendValidationRejectsZeroWhenNotAllowed(): void
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
                ->createField('conta', 'number', title: 'Número da conta', required: true, config: [
                    'allowZero' => false,
                    'minDigits' => 5,
                    'maxDigits' => 12,
                ])
                ->createField('qtd', 'number', title: 'Quantidade', required: true, config: [
                    'allowZero' => true,
                ])
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field_conta = $this->opportunityBuilder->getFieldName('conta');
        $field_qtd = $this->opportunityBuilder->getFieldName('qtd');

        /** @var Registration $registration */
        $registration = $this->registrationDirector->createDraftRegistrations($opportunity, 1)[0];
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $registration->$field_conta = 0;
        $registration->$field_qtd = 0;

        $errors = $registration->getSendValidationErrors();
        $this->assertArrayHasKey($field_conta, $errors, 'Conta com 0 deve falhar no envio');
        $this->assertArrayNotHasKey($field_qtd, $errors, 'Quantidade com 0 permitido não deve falhar por zero');

        $registration->$field_conta = 1234;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayHasKey($field_conta, $errors, 'Conta com menos dígitos que o mínimo deve falhar');

        $registration->$field_conta = 123456;
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayNotHasKey($field_conta, $errors, 'Conta válida não deve falhar');
    }

    public function testEmptyRequiredNumberFailsInsteadOfPassingAsZero(): void
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
                ->createField('conta', 'number', title: 'Conta', required: true, config: ['allowZero' => false])
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field_conta = $this->opportunityBuilder->getFieldName('conta');
        $registration = $this->registrationDirector->createDraftRegistrations($opportunity, 1)[0];
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $controller = App::i()->controller('registration');
        $controller->setEntityProperties($registration, [$field_conta => '']);
        $registration->save(true);

        $registration = App::i()->repo('Registration')->find($registration->id);
        $registration->opportunity->registerRegistrationMetadata();
        $registration->registerFieldsMetadata();

        $this->assertNull($registration->$field_conta);
        $errors = $registration->getSendValidationErrors();
        $this->assertArrayHasKey($field_conta, $errors, 'Obrigatório vazio (null) deve falhar no envio, não passar como 0');
    }
}
