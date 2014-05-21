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

    function getRandomEntity($_class, $where = null){
        $app = MapasCulturais\App::i();
        $classname = 'MapasCulturais\Entities\\' . $_class;

        $where = $where ? "AND $where" : '';
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
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) {
            $exception = $ex;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $exception, $msg);
    }


    function assertPermissionGranted($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
			$callable();
        } catch (MapasCulturais\Exceptions\PermissionDenied $ex) {
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
                $entity->save(true);
            }, "Asserting that the guest user cannot create $plural.");
        }

        /*
         * Super Admins can create entities
         */
        $this->user = 'superAdmin';


        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class){
                $entity = $this->getNewEntity($class);
                $entity->save(true);
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

                $entity->save(true);
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

                $entity->save(true);
            }, "Asserting that a super admin user can create $plural to another user.");
        }
    }



    function testCanUserModify(){
        /*
         * Asserting thar guest users cannot modify entities
         */

        $this->user = null;

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class){
                $entity = $this->getRandomEntity($class);
                $entity->shortDescription = "modificado " . uniqid();
                $entity->save(true);
            }, "Asserting that guest user cannot modify $plural");
        }


        foreach(['normal', 'staff'] as $role){
            $this->user = $role;
            /*
             * Asserting thar normal and staff users cannot modify entities of other user
             */
            foreach($this->entities as $class => $plural){
                $this->assertPermissionDenied(function() use ($class){
                    $entity = $this->getRandomEntity($class, "u.id != " . $this->app->user->id);
                    $entity->shortDescription = "modificado " . uniqid();
                    $entity->save(true);
                }, "Asserting that $role user cannot modify $plural of other user");
            }


            /*
             * Asserting thar normal and staff users can modify their own entities
             */
            foreach($this->entities as $class => $plural){
                $this->assertPermissionGranted(function() use ($class){
                    $entity = $this->getRandomEntity($class, "u.id = " . $this->app->user->id);
                    $entity->shortDescription = "modificado " . uniqid();
                    $entity->save(true);
                }, "Asserting that $role user can modify their own $plural");
            }
        }

        foreach(['admin', 'superAdmin'] as $role){
            $this->user = $role;
            /*
             * Asserting thar admin and super admin users can modify entities of other user
             */
            foreach($this->entities as $class => $plural){
                $this->assertPermissionGranted(function() use ($class){
                    $entity = $this->getRandomEntity($class, "u.id != " . $this->app->user->id);
                    $entity->shortDescription = "modificado " . uniqid();
                    $entity->save(true);
                }, "Asserting that $role user cannot modify $plural of other user");
            }
        }


    }

    function testCanUserRemove(){ }

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
                $entity->save(true);
            }, "Asserting that a guest user cannot verify $plural.");
        }


        /*
         * Asserting that normal users cannot verify entities
         */

        $this->user = 'normal';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getNewEntity($class);
                $entity->save(true);

                $entity->verify();
                $entity->save(true);
            }, "Asserting that a normal user cannot verify their own $plural.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
                $entity->verify();
                $entity->save(true);
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
                $entity->save(true);
            }, "Asserting that a staff user cannot verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getNewEntity($class);
                $entity->save(true);

                $entity->verify();
                $entity->save(true);
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
                $entity->save(true);
            }, "Asserting that a admin user can verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getNewEntity($class);
                $entity->save(true);

                $entity->verify();
                $entity->save(true);
            }, "Asserting that a staff user can verify their own $plural.");
        }
    }

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
        $roles = ['staff', 'admin', 'superAdmin'];

        /*
         * Guest user cannot add or remove roles
         */
        $this->user = null;

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that guest user cannot add the role $role to a user");
        }

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that guest user cannot remove the role $role of a user");
        }


        /*
         * Normal user cannot add or remove roles
         */
        $this->user = 'normal';

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that normal user cannot add the role $role to a user");
        }

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that normal user cannot remove the role $role of a user");
        }


        /*
         * Admin user can add and remove role staff
         */
        $this->user = 'admin';

        foreach($roles as $role){
            $this->resetTransactions();

            switch ($role) {
                case 'staff':
                    $assertion = 'assertPermissionGranted';
                    $can = 'can';
                break;

                default:
                    $assertion = 'assertPermissionDenied';
                    $can = 'cannot';
                break;
            }

            $this->$assertion(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that admin user $can add the role $role to a user");
        }

        foreach($roles as $role){
            $this->resetTransactions();

            switch ($role) {
                case 'staff':
                    $assertion = 'assertPermissionGranted';
                    $can = 'can';
                break;

                default:
                    $assertion = 'assertPermissionDenied';
                    $can = 'cannot';
                break;
            }

            $this->$assertion(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that admin user $can remove the role $role of a user");
        }

        /*
         * Admin user can add and remove role staff
         */
        $this->user = 'superAdmin';

        foreach($roles as $role){
            $this->resetTransactions();

            $this->assertPermissionGranted(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that super admin user can add the role $role to a user");
        }

        foreach($roles as $role){
            $this->resetTransactions();

            $this->assertPermissionGranted(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that super admin user can remove the role $role of a user");
        }
    }
}