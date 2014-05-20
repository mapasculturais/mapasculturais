<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of PermissionsTest
 *
 * @author rafael
 */
class PermissionsTest extends MapasCulturais_TestCase{
    function getNewEntity($class){
        $app = MapasCulturais\App::i();
        $classname = 'MapasCulturais\Entities\\' . $class;

        $type = array_shift($app->getRegisteredEntityTypes($classname));

        $entity = new $classname;
        $entity->name = "Test $class "  . uniqid();
        $entity->type = $type;
        $entity->shortDescription = 'A litle short description';

        return $entity;
    }

    function testCanUserCreate(){
        /*
         * Guest users cant create entities.
         */
        $this->user = null;

        // Spaces
        $ex = null;
        try{
            $entity = $this->getNewEntity('Space');
            $entity->save();
        } catch (Exception $ex) { }
        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create spaces.');

        // Agents
        $ex = null;
        try{
            $entity = $this->getNewEntity('Agent');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create agents.');

        // Project
        $ex = null;
        try{
           $entity = $this->getNewEntity('Project');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create projects.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create agents.');


        /*
         * Super Admins can create entities
         */
        $this->user = 'superAdmin';

        // Spaces
        $ex = null;
        try{
            $entity = $this->getNewEntity('Space');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create spaces.');

        // Agents
        $ex = null;
        try{
            $entity = $this->getNewEntity('Agent');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create agents.');

        // Project
        $ex = null;
        try{
            $entity = $this->getNewEntity('Project');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create projects.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->save();
        } catch (Exception $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create events.');


    }
}
