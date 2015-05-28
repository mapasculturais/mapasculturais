<?php

require_once 'bootstrap.php';

require 'Entity.inc.TestEntities.php';

class EntityTests extends MapasCulturais_TestCase {

    function testValidations() {
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

    function testDirectCircularReference() {
        $entities = ['Agent', 'Space', 'Project'];

        foreach ($entities as $class) {
            $this->user = 'normal';

            $e1 = $this->getNewEntity($class);
            $e1->save(true);

            $e2 = $this->getNewEntity($class);
            $e2->parent = $e1;
            $e2->save(true);

            $e1->parent = $e2;

            $errors = $e1->validationErrors;

            $this->assertArrayHasKey('parent', $errors, 'Asserting that direct (2 objects) circular references is not allowed.');
        }
    }

    function testIndirectCircularReference() {
        $entities = ['Agent', 'Space', 'Project'];
        foreach ($entities as $class) {
            $this->user = 'normal';

            $e1 = $this->getNewEntity($class);
            $e1->save(true);

            $e2 = $this->getNewEntity($class);
            $e2->parent = $e1;
            $e2->save(true);

            $e3 = $this->getNewEntity($class);
            $e3->parent = $e2;
            $e3->save(true);

            $e1->parent = $e3;

            $errors = $e1->validationErrors;

            $this->assertArrayHasKey('parent', $errors, 'Asserting that indirect (more then 2 object) circular references is not allowed.');
        }
    }

    function testParentType() {
        $entities = ['Space', 'Project'];
        foreach ($entities as $class) {
            $this->user = 'normal';

            $e1 = $this->getNewEntity($class);

            $e1->parent = $this->getNewEntity('Agent');

            $errors = $e1->validationErrors;

            $this->assertArrayHasKey('parent', $errors, 'Asserting that the type of parent entity must be of the same type of the child entity.');
        }
    }

    function testParentIsNotChild() {
        $entities = ['Space', 'Project'];
        foreach ($entities as $class) {
            $this->user = 'normal';

            $e1 = $this->getNewEntity($class);

            $e1->parent = $e1;

            $errors = $e1->validationErrors;

            $this->assertArrayHasKey('parent', $errors, 'Asserting that the parent could not be the child.');
        }
    }

}
