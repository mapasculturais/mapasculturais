<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use Tests\Abstract\TestCase;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

class RegistrationConfigurationSerializationTest extends TestCase
{
    use OpportunityBuilder,
        UserDirector;

    function testRequiredIsSerializedAsBooleanForFieldsAndFiles(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $field = new RegistrationFieldConfiguration();
        $field->owner = $opportunity;
        $field->title = 'Campo de teste';
        $field->fieldType = 'text';
        $field->save(true);

        $file = new RegistrationFileConfiguration();
        $file->owner = $opportunity;
        $file->title = 'Anexo de teste';
        $file->save(true);

        $field->required = 1;
        $file->required = 'true';

        $fieldJson = $field->jsonSerialize();
        $fileJson = $file->jsonSerialize();

        $this->assertIsBool($fieldJson['required']);
        $this->assertTrue($fieldJson['required']);
        $this->assertIsBool($fileJson['required']);
        $this->assertTrue($fileJson['required']);

        $field->required = 'false';
        $file->required = 'false';

        $this->assertFalse($field->jsonSerialize()['required']);
        $this->assertFalse($file->jsonSerialize()['required']);
    }
}
