<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of PermissionsTest
 *
 * @author rafael
 */
class PermissionsTest extends MapasCulturais_TestCase{
    protected $entities = [
        'Agent' => 'agents',
        'Space' => 'spaces',
        'Event' => 'events',
        'Project' => 'projects'
    ];

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

    function getRandomEntity($_class, $where){
        $app = MapasCulturais\App::i();
        $classname = 'MapasCulturais\Entities\\' . $_class;

        $where = "AND $where";
        if($_class === 'User')
            return $this->app->em->createQuery("SELECT e FROM $classname e WHERE e.status > 0 $where")->setMaxResults(1)->getOneOrNullResult();
        else if($_class === 'Agent')
            return $this->app->em->createQuery("SELECT e FROM $classname e JOIN e.user u WHERE e.status > 0 $where")->setMaxResults(1)->getOneOrNullResult();
        else
            return $this->app->em->createQuery("SELECT e FROM $classname e JOIN e.owner a JOIN a.user u WHERE e.status > 0 $where")->setMaxResults(1)->getOneOrNullResult();
    }

    function assertPermissionDenied($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
			$callable();
        } catch (Exception $ex) {
            $exception = $ex;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $exception, $msg);
    }


    function assertPermissionGranted($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
			$callable();
        } catch (Exception $ex) {
            $exception = $ex;
        }

        $this->assertEmpty($exception, $msg);
    }

    function testCanUserCreate(){
        $app = MapasCulturais\App::i();

        /*
         * Guest users cant create entities.
         */
        $this->user = null;

        foreach($this->entities as $class => $plural){
            if($class === 'Agent')
                continue;

            $this->assertPermissionDenied(function() use ($class){
                $entity = $this->getNewEntity($class);
                $entity->save();
            }, "Asserting that the guest user cannot create $plural.");
        }

        /*
         * Super Admins can create entities
         */
        $this->user = 'superAdmin';


        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class){
                $entity = $this->getNewEntity($class);
                $entity->save();
            }, "Asserting that a super admin user can create $plural.");
        }


        /*
         * Normal users cannot create entities to another users
         */
        $this->user = 'normal';

        $another_user = $this->getRandomEntity('User', 'e.id != ' . $app->user->id);

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $another_user){
                $entity = $this->getNewEntity($class);

                if($class === 'Agent'){
                    $entity->user = $another_user;
                }else
                    $entity->ownerId = $another_user->profile->id;

                $entity->save();
            }, "Asserting that a normal user cannot create $plural to another user.");
        }

        /*
         * Super Admins can create entities to another users
         */
        $this->user = 'superAdmin';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $another_user){
                $entity = $this->getNewEntity($class);

                if($class === 'Agent')
                    $entity->user = $another_user;
                else
                    $entity->ownerId = $another_user->profile->id;

                $entity->save();
            }, "Asserting that a super admin user can create $plural to another user.");
        }
    }

    function testCanUserVerifyEntity(){
        $app = $this->app;

        $this->user = null;

        /*
         * Asserting that guest users cannot verify entities
         */

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class){
                $entity = $this->getRandomEntity('Agent', 'e.isVerified = false');
                $entity->verify();
                $entity->save();
            }, "Asserting that a guest user cannot verify $plural.");
        }


        /*
         * Asserting that normal users cannot verify entities
         */
        $this->user = 'normal';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id = ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a normal user cannot verify their own $plural.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a normal user cannot verify $plural of other user.");
        }


        /*
         * Asserting that staff users can verify entities
         */

        $this->user = 'staff';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id != ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a staff user cannot verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id = ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a staff user can verify their own $plural.");
        }


        /*
         * Asserting that admin users can verify entities
         */

        $this->user = 'admin';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id != ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a admin user can verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id = ' . $app->user->id);
                $entity->verify();
                $entity->save();
            }, "Asserting that a staff user can verify their own $plural.");
        }
    }

    function testCanUserModify(){
        /*
         * Asserting thar guest users cannot modify entities
         */


    }

    function testCanUserRemove(){ }

    function testCanUserViewPrivateData(){ }

    function testAgentRelationsPermission(){
        // create agent relation without control

        // create agent relation withcontrol

        // remove agent relation without control

        // remove agent relation with control
    }

    function testProjectRegistrationPermissions(){
        // approve registration

        // reject registration
    }

    function testFilesPermissions(){

    }

    function testMetalistPermissions(){

    }

    function testCanUserAddRemoveRole(){
        // add role
        // remove role
    }
}