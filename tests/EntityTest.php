<?php
require_once 'bootstrap.php';

require 'Entity.inc.TestEntities.php';

class EntityTests extends MapasCulturais_TestCase{
    function testValidations(){
        $entity = new TestEntity;


        $this->assertArrayHasKey('requiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));
        $this->assertArrayNotHasKey('notRequiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));

        $entity->notRequiredBrPhone = 'Invalid Phone';
        $this->assertArrayHasKey('requiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));
        $this->assertArrayHasKey('notRequiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));

        $entity->notRequiredBrPhone = '(11) 3333-3333';
        $this->assertArrayHasKey('requiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));
        $this->assertArrayNotHasKey('notRequiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));

        $entity->notRequiredBrPhone = '(11) 3333-3333';
        $this->assertArrayHasKey('requiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));
        $this->assertArrayNotHasKey('notRequiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));

        $entity->notRequiredBrPhone = null;
        $this->assertArrayHasKey('requiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));
        $this->assertArrayNotHasKey('notRequiredBrPhone', $entity->validationErrors, print_r($entity->validationErrors, true));

        $entity->requiredBrPhone = '(11) 3333-3333';
        $this->assertEmpty($entity->validationErrors, print_r($entity->validationErrors, true));

    }
}
