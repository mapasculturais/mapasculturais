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

    function getRandomEntity($class, $where){
        $app = MapasCulturais\App::i();
        $classname = 'MapasCulturais\Entities\\' . $class;

        $where = "AND $where";

        return $this->app->em->createQuery("SELECT e FROM $classname e WHERE e.status > 0 $where")->setMaxResults(1)->getOneOrNullResult();
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
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }
        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create spaces.');

//        // Agents
//        $ex = null;
//        try{
//            $entity = $this->getNewEntity('Agent');
//            $entity->save();
//        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }
//
//        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create agents.');

        // Project
        $ex = null;
        try{
           $entity = $this->getNewEntity('Project');
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that the guest user cannot create projects.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

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
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create spaces.');

        // Agents
        $ex = null;
        try{
            $entity = $this->getNewEntity('Agent');
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create agents.');

        // Project
        $ex = null;
        try{
            $entity = $this->getNewEntity('Project');
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create projects.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create events.');



        /*
         * Normal users cannot create entities to another users
         */
        $this->user = 'normal';

        $another_user = $this->app->repo('User')->find(1);

        // Spaces
        $ex = null;
        try{
            $entity = $this->getNewEntity('Space');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot create spaces to another user.');

        // Agents
        $ex = null;
        try{
            $entity = $this->getNewEntity('Agent');
            $entity->ownerId = $another_user->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot create agents to another user.');

        // Project
        $ex = null;
        try{
            $entity = $this->getNewEntity('Project');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot create projects to another user.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot create events to another user.');


        /*
         * Super Admins can create entities to another users
         */
        $this->user = 'superAdmin';

        // Spaces
        $ex = null;
        try{
            $entity = $this->getNewEntity('Space');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create spaces to another user.');

        // Agents
        $ex = null;
        try{
            $entity = $this->getNewEntity('Agent');
            $entity->ownerId = $another_user->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create agents to another user.');

        // Project
        $ex = null;
        try{
            $entity = $this->getNewEntity('Project');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create projects to another user.');

        // Event
        $ex = null;
        try{
            $entity = $this->getNewEntity('Event');
            $entity->ownerId = $another_user->profile->id;
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a superAdmin user can create events to another user.');
    }

    function testCanUserVerifyEntity(){
        $app = $this->app;

        $this->user = null;

        /*
         * Asserting that guest users cannot verify entities
         */

        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false');
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a guest user cannot verify entities.');

        /*
         * Asserting that normal users cannot verify entities
         */

        $this->user = 'normal';

        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot verify entities of another user.');



        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId = ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a normal user cannot verify their own entities.');



        /*
         * Asserting that staff users can verify entities
         */

        $this->user = 'staff';

        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $ex, 'Asserting that a staff user cannot verify entities of another user.');

        // Space
        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId = ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a staff user can verify their own entities.');



        /*
         * Asserting that staff users can verify entities
         */

        $this->user = 'admin';

        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a admin user can verify entities of another user.');

        // Space
        $ex = null;
        try{
            $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId = ' . $app->user->id);
            $entity->verify();
            $entity->save();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) { }

        $this->assertEmpty($ex, 'Asserting that a admin user can verify their own entities.');

    }
}
