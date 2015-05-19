<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of PermissionsTest
 *
 * @author rafael
 */
class PermissionsTest extends MapasCulturais_TestCase{

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

    function testCanUserCreate(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
        $app = MapasCulturais\App::i();

        /*
         * Guest users CANNOT create entities.
         */
        $this->user = null;

        foreach($this->entities as $class => $plural){
            if($class === 'Agent')
                continue;

            $this->assertPermissionDenied(function() use ($class){
                $entity = $this->getNewEntity($class);
                $entity->save(true);
            }, "Asserting that the guest user CANNOT create $plural.");
        }

        /*
         * Super Admins CAN create entities
         */
        $this->user = 'superAdmin';


        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class){
                $entity = $this->getNewEntity($class);
                $entity->save(true);
            }, "Asserting that a super admin user CAN create $plural.");
        }


        /*
         * Normal users CANNOT create entities to another users
         */
        $this->user = 'normal';

        $another_user = $this->getRandomEntity('User', 'e.id != ' . $app->user->id);

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $another_user){
                $entity = $this->getNewEntity($class);

                $entity->owner = $another_user->profile;

                $entity->save(true);

            }, "Asserting that a normal user CANNOT create $plural to another user.");
        }

        /*
         * Super Admins CAN create entities to another users
         */
        $this->user = 'superAdmin';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $another_user){
                $entity = $this->getNewEntity($class);

                $entity->owner = $another_user->profile;

                $entity->save(true);
            }, "Asserting that a super admin user CAN create $plural to another user.");
        }
        $this->app->enableWorkflow();
    }


    function testCanUserModify(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
        /*
         * Asserting thar guest users cannot modify entities
         */

        $this->user = null;

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class){
                $entity = $this->getRandomEntity($class);
                $entity->shortDescription = "modificado " . uniqid();
                $entity->save(true);
            }, "Asserting that guest user CANNOT modify $plural");
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
                }, "Asserting that $role user CANNOT modify $plural of other user");
            }


            /*
             * Asserting thar normal and staff users can modify their own entities
             */
            foreach($this->entities as $class => $plural){
                $this->assertPermissionGranted(function() use ($class){
                    $entity = $this->getRandomEntity($class, "u.id = " . $this->app->user->id);
                    $entity->shortDescription = "modificado " . uniqid();
                    $entity->save(true);
                }, "Asserting that $role user CAN modify their own $plural");
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
                }, "Asserting that $role user CANNOT modify $plural of other user");
            }
        }

        $this->app->enableWorkflow();
    }

    function testCanUserRemove(){
        $this->app->disableWorkflow();
        $this->app->enableWorkflow();
    }

    function testCanUserVerifyEntity(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
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
            }, "Asserting that a guest user CANNOT verify $plural.");
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
            }, "Asserting that a normal user CANNOT verify their own $plural.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity('Agent', 'e.isVerified = false AND e.userId != ' . $app->user->id);
                $entity->verify();
                $entity->save(true);
            }, "Asserting that a normal user CANNOT verify $plural of other user.");
        }


        /*
         * Asserting that staff users can verify entities
         */

        $this->resetTransactions();

        $this->user = 'staff';

        foreach($this->entities as $class => $plural){
            $this->assertPermissionDenied(function() use ($class, $app){
                $entity = $this->getRandomEntity($class, 'e.isVerified = false AND u.id != ' . $app->user->id);
                if(!$entity){
                    var_dump(array($class, $app->user->id));

                }
                $entity->verify();
                $entity->save(true);
            }, "Asserting that a staff user CANNOT verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getNewEntity($class);
                $entity->save(true);

                $entity->verify();
                $entity->save(true);
            }, "Asserting that a staff user CAN verify their own $plural.");
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
            }, "Asserting that a admin user CAN verify $plural of other user.");
        }

        foreach($this->entities as $class => $plural){
            $this->assertPermissionGranted(function() use ($class, $app){
                $entity = $this->getNewEntity($class);
                $entity->save(true);

                $entity->verify();
                $entity->save(true);
            }, "Asserting that a staff user CAN verify their own $plural.");
        }
        $this->app->enableWorkflow();
    }

    function testCanUserViewPrivateData(){
        $this->app->disableWorkflow();
        $this->app->enableWorkflow();
    }

    function testAgentRelationsPermissions(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
        // create agent relation without control

        // create agent relation withcontrol

        // remove agent relation without control

        // remove agent relation with control

        // assert that related agent with control cannot change the entity owner

        $GROUP = 'group 1';

        $user1 = function (){ return $this->getUser('normal', 0); };
        $user2 = function (){ return $this->getUser('normal', 1); };
        $user3 = function (){ return $this->getUser('staff', 0); };
        /*
         * Asserting that owner user and a related agent with control can modify an entity
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $entities = $class == 'Agent' ? $user1()->$plural : $user1()->profile->$plural;

            $entity = $entities[0];

            $this->assertTrue($entity->canUser('modify', $user1()), "Asserting that user CAN modify his own $class before the relation is created.");
            $this->assertFalse($entity->canUser('modify', $user2()), "Asserting that user 2 CANNOT modify the $class before the relation is created.");
            $this->assertFalse($entity->canUser('modify', $user3()), "Asserting that user 3 CANNOT modify the $class before the relation is created.");


            // login with user1
            $this->user = $user1();

            // create the realation with control
            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            // logout
            $this->user = null;

            $this->assertTrue($entity->canUser('modify', $user1()), "Asserting that user CAN modify his own $class after the relation is created.");
            $this->assertTrue($entity->canUser('modify', $user2()), "Asserting that user 2 CAN modify the $class after the relation is created.");
            $this->assertFalse($entity->canUser('modify', $user3()), "Asserting that user 3 CANNOT modify the $class after the relation is created.");

            $this->resetTransactions();
        }


        /*
         * Asserting that only the owner user can modify the agentId (the owner) of the entity
         */

        $new_agent1 = $this->getNewEntity('Agent', $user1());
        $new_agent2 = $this->getNewEntity('Agent', $user2());
        $new_agent3 = $this->getNewEntity('Agent', $user3());

        $new_agent1->save(true);
        $new_agent2->save(true);
        $new_agent3->save(true);

        foreach($this->entities as $class => $plural){
            if($class == 'Agent')
                continue;

            $entities = $user1()->$plural;
            $entity = $entities[0];


            $this->user = 'admin';
            $this->assertPermissionGranted(function() use($entity){
                $old_owner = $entity->owner;
                $entity->owner = $this->app->user->profile;
                $entity->save(true);

                $entity->owner = $old_owner;
                $entity->save(true);

            }, "Asserting that an admin user CAN modify the $class owner");

            $this->user = $user1();
            $this->assertPermissionGranted(function() use($entity, $new_agent1){
                $entity->owner = $new_agent1;
                $entity->save(true);
            }, "Asserting that the user CAN modify his own $class owner before the relation is created.");

            $this->user = $user2();
            $this->assertPermissionDenied(function() use($entity, $new_agent2){
                $entity->owner = $new_agent2;
                $entity->save(true);
            }, "Asserting that the user 2 CANNOT modify the $class owner before the relation is created.");

            $this->user = $user3();
            $this->assertPermissionDenied(function() use($entity, $new_agent3){
                $entity->owner = $new_agent3;
                $entity->save(true);
            }, "Asserting that the user 3 CANNOT modify the $class owner before the relation is created.");


            // login with user1
            $this->user = $user1();

            // create the realation with control
            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->assertPermissionGranted(function() use($entity, $new_agent1){
                $entity->owner = $new_agent1;
                $entity->save(true);
            }, "Asserting that the user CAN modify his own $class owner before the relation is created.");

            $this->user = $user2();
            $this->assertPermissionDenied(function() use($entity, $new_agent2){
                $entity->owner = $new_agent2;
                $entity->save(true);
            }, "Asserting that the user 2, now with control, CANNOT modify the $class owner AFTER the relation is created.");

            $this->user = $user3();
            $this->assertPermissionDenied(function() use($entity, $new_agent3){
                $entity->owner = $new_agent3;
                $entity->save(true);
            }, "Asserting that the user 3 CANNOT modify the $class owner after the relation is created.");
        }

        $this->resetTransactions();

        /*
         * Asserting that only the owner user can remove an entity
         */

        foreach($this->entities as $class => $plural){
            $this->user = $user1();

            if($class == 'Agent'){
                $entity = $this->getNewEntity($class);;
                $entity->save(true);
            }else{
                $entities = $user1()->$plural;
                $entity = $entities[0];
            }


            $this->assertTrue($entity->canUser('remove', $user1()), "Asserting that user CAN remove his own $class before the relation is created.");
            $this->assertFalse($entity->canUser('remove', $user2()), "Asserting that user 2 CANNOT remove the $class before the relation is created.");
            $this->assertFalse($entity->canUser('remove', $user3()), "Asserting that user 3 CANNOT remove the $class before the relation is created.");

            // create the realation with control
            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            // logout
            $this->user = null;

            $this->assertTrue($entity->canUser('remove', $user1()), "Asserting that user CAN remove his own $class after the relation is created.");
            $this->assertFalse($entity->canUser('remove', $user2()), "Asserting that user 2, now with control, CANNOT remove the $class after the relation is created.");
            $this->assertFalse($entity->canUser('remove', $user3()), "Asserting that user 3 CANNOT remove the $class after the relation is created.");
        }

        $this->resetTransactions();

        /*
         *  Asserting that user with control can create spaces, projects and events owned by the controlled agent.
         */
        foreach($this->entities as $class => $plural){
            if($class == 'Agent')
                continue;

            $this->assertPermissionGranted(function() use($user1, $class){
                $this->user = $user1();
                $entity = $this->getNewEntity($class, $user1());
                $entity->save(true);
            }, "Asserting that user CAN create $plural owned by his own agent before the relation is created");

            $this->assertPermissionDenied(function() use($user1, $user2, $class){
                $this->user = $user2();
                $entity = $this->getNewEntity($class);
                $entity->owner = $user1()->profile;
                $entity->save(true);
            }, "Asserting that user 2 CANNOT create $plural owned by user 1 before the relation is created");

            $this->assertPermissionDenied(function() use($user1, $user3, $class){
                $this->user = $user3();
                $entity = $this->getNewEntity($class);
                $entity->owner = $user1()->profile;
                $entity->save(true);
            }, "Asserting that user 3 CANNOT create $plural owned by user 1 before the relation is created");


            $this->user = $user1();
            $agent = $user1()->profile;
            $agent->createAgentRelation($user2()->profile, $GROUP, true, true);


            $this->assertPermissionGranted(function() use($user1, $class){
                $this->user = $user1();
                $entity = $this->getNewEntity($class, $user1());
                $entity->save(true);
            }, "Asserting that user CAN create $plural owned by his own agent after the relation is created");

            $this->assertPermissionGranted(function() use($user1, $user2, $class){
                $this->user = $user2();
                $entity = $this->getNewEntity($class);
                $entity->owner = $user1()->profile;
                $entity->save(true);
            }, "Asserting that user 2, now with control, CAN create $plural owned by user 1 after the relation is created");

            $this->assertPermissionDenied(function() use($user1, $user3, $class){
                $this->user = $user3();
                $entity = $this->getNewEntity($class);
                $entity->owner = $user1()->profile;
                $entity->save(true);
            }, "Asserting that user 3 CANNOT create $plural owned by user 1 after the relation is created");

            $this->resetTransactions();
        }
        /*
         *  Asserting that user with control can remove spaces, projects and events owned by the controlled agent.
         */
        foreach($this->entities as $class => $plural){
            if($class == 'Agent')
                continue;


            $this->assertPermissionGranted(function() use($user1, $plural){
                $this->user = $user1();

                $entities = $user1()->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user CAN remove his own $plural before the relation is created");

            $this->resetTransactions();

            $this->assertPermissionDenied(function() use($user1, $user2, $plural){
                $this->user = $user2();

                $entities = $user1()->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user 2 CANNOT remove $plural owned by user 1 before the relation is created");

            $this->resetTransactions();

            $this->assertPermissionDenied(function() use($user1, $user3, $plural){
                $this->user = $user3();

                $entities = $user1()->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user 3 CANNOT remove $plural owned by user 1 before the relation is created");

            $this->resetTransactions();
            $this->user = $user1();
            $user1()->profile->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->assertPermissionGranted(function() use($user1, $plural){
                $this->user = $user1();

                $entities = $user1()->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user CAN remove his own $plural before the relation is created");

            $this->resetTransactions();
            $this->user = $user1();
            $user1()->profile->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->assertPermissionGranted(function() use($user1, $user2, $user3, $plural){
                $this->user = $user2();

                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user 2, now with control, CAN remove $plural owned by user 1 after the relation is created");

            $this->resetTransactions();
            $this->user = $user1();
            $user1()->profile->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->assertPermissionDenied(function() use($user1, $user3, $plural){
                $this->user = $user3();

                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
                $entity->delete(true);

            }, "Asserting that user 3 CANNOT remove $plural owned by user 1 after the relation is created");

            $this->resetTransactions();
        }

        /*
         *  Asserting that an user with control can create agent relations
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $this->user = $user1();

            if($class === 'Agent'){
                $entity = $user1()->profile;
            }else{
                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
            }

            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->user = $user2();
            $this->assertPermissionGranted(function() use ($entity, $user2, $GROUP){
                $entity->createAgentRelation($user2()->profile, $GROUP, false, true);

            }, "Asserting that user CAN create agent relations with $plural that he has control");

        }

        /*
         *  Asserting that an user with control can remove agent relations of agents without control
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $this->user = $user1();

            if($class === 'Agent'){
                $entity = $user1()->profile;
            }else{
                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
            }

            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $entity->createAgentRelation($user3()->profile, $GROUP, false, true);

            $this->user = $user2();
            $this->assertPermissionGranted(function() use ($entity, $user3, $GROUP){
                $entity->removeAgentRelation($user3()->profile, $GROUP, false, true);

            }, "Asserting that user CAN remove agent relations with $plural that he has control");

        }

        /*
         *  Asserting that an user with control CANNOT remove agent relations of agents with control
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $this->user = $user1();

            if($class === 'Agent'){
                $entity = $user1()->profile;
            }else{
                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
            }

            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $entity->createAgentRelation($user3()->profile, $GROUP, true, true);

            $this->user = $user2();
            $this->assertPermissionDenied(function() use ($entity, $user3, $GROUP){
                $entity->removeAgentRelation($user3()->profile, $GROUP, false, true);

            }, "Asserting that user CANNOT remove agent relations with $plural that he has control");
        }

        /*
         *  Asserting that an user with control cannot give control to a related agent
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $this->user = $user1();

            if($class === 'Agent'){
                $entity = $user1()->profile;
            }else{
                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
            }

            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $entity->createAgentRelation($user3()->profile, $GROUP, false, true);

            $this->user = $user2();
            $this->assertPermissionDenied(function() use ($entity, $user3){
                $entity->setRelatedAgentControl($user3()->profile, true);

            }, "Asserting that user CANNOT give control to other related agent in $plural that he has control");
        }

        /*
         *  Asserting that an user with control cannot remove control of a related agent
         */

        foreach($this->entities as $class => $plural){
            $this->resetTransactions();
            $this->user = $user1();

            if($class === 'Agent'){
                $entity = $user1()->profile;
            }else{
                $entities = $user1()->profile->$plural;
                $entity = $entities[0];
            }

            $entity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $entity->createAgentRelation($user3()->profile, $GROUP, true, true);

            $this->user = $user2();
            $this->assertPermissionDenied(function() use ($entity, $user3){
                $entity->setRelatedAgentControl($user3()->profile, false);

            }, "Asserting that user CANNOT remove control of other related agent in $plural that he has control");
        }


        /**
         * Asserting that an user with control over a space or project can control children spaces or projects
         */

        foreach(array('Space' => 'spaces', 'Project' => 'projects') as $class => $plural){
            $this->resetTransactions();

            $this->user = $user1();

            $parentEntity = $this->getNewEntity($class);
            $parentEntity->owner = $user1()->profile;
            $parentEntity->save();

            $childEntity = $this->getNewEntity($class);
            $childEntity->owner = $user1()->profile;
            $childEntity->parent = $parentEntity;
            $childEntity->save();

            $parentEntity->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->user = $user2();

            $this->assertTrue($childEntity->userHasControl($user2()), "Asserting that an user with control over a parent $class CAN have over child a $class");
            $this->assertTrue($childEntity->canUser('modify'), "Asserting that an user with control over a parent $class CAN modify a child $class");
            $this->assertTrue($childEntity->canUser('createAgentRelation'), "Asserting that an user with control over a parent $class CAN create agent relations to a child $class");
            $this->assertTrue($childEntity->canUser('createChild'), "Asserting that an user with control over a parent $class CAN create children for the child $class");

            // somente quem controla o owner pode remover. quem controla o parent não.
            $this->assertFalse($childEntity->canUser('remove'), "Asserting that an user with control over a parent $class CANNOT remove a child $class");
        }


        /**
         * Asserting that an user with control over an agent CONTROL and CAN REMOVE spaces, events and projects of the controlled agent
         */

        foreach($this->entities as $class => $plural){
            if($class === 'Agent')
                continue;

            $this->resetTransactions();

            $this->user = $user1();

            $entity = $this->getNewEntity($class);
            $entity->owner = $user1()->profile;
            $entity->save();

            $user1()->profile->createAgentRelation($user2()->profile, $GROUP, true, true);

            $this->user = $user2();

            $this->assertTrue($entity->userHasControl($user2()), "Asserting that an user with control over the owner agent CAN CONTROL $plural of this controlled agent");
            $this->assertTrue($entity->canUser('modify'), "Asserting that an user with control over the owner agent CAN modify $plural of this controlled agent");
            $this->assertTrue($entity->canUser('createAgentRelation'), "Asserting that an user with control over the owner agent CAN create agent relations to $plural of this controlled agent");
            if($class != 'Event')
                $this->assertTrue($childEntity->canUser('createChild'), "Asserting that an user with control over the owner agent CAN create children for $plural of this controlled agent");

            // somente quem controla o owner pode remover. quem controla o parent não.
            $this->assertTrue($entity->canUser('remove'), "Asserting that an user with control over the owner agent CAN remove $plural of this controlled agent");

        }

        $this->resetTransactions();
        $this->app->enableWorkflow();
    }

    function testEventOccurrencePermissions(){
        $this->app->disableWorkflow();
        $this->resetTransactions();

        $rule = array(
            'startsAt' => '11:11',
            'duration' => '01h00',
            'frequency' => 'once',
            'startsOn' => '2014-07-16',
            'until' => '',
            'description' => 'das 11:11 às 12:11 do dia 16 de Julho',
            'price' => 'R$11,11'
        );

        $user0 = $this->getUser('normal', 0);
        $user1 = $this->getUser('normal', 1);

        $space = $user1->spaces[0];

        // Asserting that a normal user CANNOT create an event occurrence on spaces that he don't have control
        $this->user = $user0;

        $event = $this->getNewEntity('Event');
        $event->owner = $user0->profile;
        $event->save();

        $this->assertPermissionDenied(function() use($event, $space, $rule){
            $occ = new \MapasCulturais\Entities\EventOccurrence;

            $occ->event = $event;
            $occ->space = $space;
            $occ->rule = $rule;

            $occ->save();
        }, "Asserting that a normal user CANNOT create an event occurrence on spaces that he don't have control");


        // Asserting that a normal user CAN create an event occurrence on spaces that he have control
        $this->user = $user1;

        $space->createAgentRelation($user0->profile, "AGENTS WITH CONTROL", true, true);

        $this->user = $user0;

        $this->assertPermissionGranted(function() use($event, $space, $rule){
            $occ = new \MapasCulturais\Entities\EventOccurrence;

            $occ->event = $event;
            $occ->space = $space;
            $occ->rule = $rule;

            $occ->save();
        }, "Asserting that a normal user CAN create an event occurrence on spaces that he have control");

        // Assert that a normal user CAN create an event occurrence in public spaces that he don't have control
        $this->user = $user0;
        $public_space = $this->getNewEntity('Space');
        $public_space->owner = $user0->profile;
        $public_space->public = true;
        $public_space->save();

        $this->user = $user1;

        $event = $this->getNewEntity('Event');
        $event->owner = $user1->profile;
        $event->save();


        $this->assertPermissionGranted(function() use($event, $public_space, $rule){
            $occ = new \MapasCulturais\Entities\EventOccurrence;

            $occ->event = $event;
            $occ->space = $public_space;
            $occ->rule = $rule;

            $occ->save();
        }, "Asserting that a normal user CAN create an event occurrence in public spaces that he don't have control");

        $this->app->enableWorkflow();
    }

    function testProjectEventCreation(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
        // assert that a user WITHOUT control of a project CANNOT create events to this project
        $user1 = $this->getUser('normal', 0);
        $user2 = $this->getUser('normal', 1);

        $project = $user2->projects[0];

        $this->user = $user1;

        $this->assertPermissionDenied(function() use($project){
            $event = $this->getNewEntity('Event');
            $event->project = $project;
            $event->save();
        }, 'Asserting that a user WITHOUT control of a project CANNOT create events to this project');


        // assert that a user WITH control of a project CAN create events to this project
        $this->user = $user2;

        $project->createAgentRelation($user1->profile, "AGENTS WITH CONTROL", true, true);

        $this->user = $user1;

        $this->assertPermissionGranted(function() use($project){
            $event = $this->getNewEntity('Event');
            $event->project = $project;
            $event->save();
        }, 'Asserting that a user WITH control of a project CAN create events to this project');
        $this->app->enableWorkflow();
    }

    function testFilesPermissions(){
        $this->app->disableWorkflow();
        $this->app->enableWorkflow();
    }

    function testMetalistPermissions(){
        $this->app->disableWorkflow();
        $this->app->enableWorkflow();
    }

    function testCanUserAddRemoveRole(){
        $this->app->disableWorkflow();
        $this->resetTransactions();
        $roles = ['staff', 'admin', 'superAdmin'];

        /*
         * Guest user cannot add or remove roles
         */
        $this->user = null;

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that guest user CANNOT add the role $role to a user");
        }

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that guest user CANNOT remove the role $role of a user");
        }


        /*
         * Normal user cannot add or remove roles
         */
        $this->user = 'normal';

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser('normal', 1);
                $user->addRole($role);
            }, "Asserting that normal user CANNOT add the role $role to a user");
        }

        foreach($roles as $role){
            $this->assertPermissionDenied(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that normal user CANNOT remove the role $role of a user");
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
                    $can = 'CAN';
                break;

                default:
                    $assertion = 'assertPermissionDenied';
                    $can = 'CANNOT';
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
                    $can = 'CAN';
                break;

                default:
                    $assertion = 'assertPermissionDenied';
                    $can = 'CANNOT';
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
            }, "Asserting that super admin user CAN add the role $role to a user");
        }

        foreach($roles as $role){
            $this->resetTransactions();

            $this->assertPermissionGranted(function() use($role){
                $user = $this->getUser($role, 1);
                $user->removeRole($role);
            }, "Asserting that super admin user CAN remove the role $role of a user");
        }
        $this->app->enableWorkflow();
    }

    function testSoftDeleteDestroy(){
        foreach(['normal', 'staff', 'admin'] as $role){
            foreach ($this->entities as $class => $plural){
                $this->resetTransactions();
                $user = $this->getUser($role, 1);
                $this->user = $user;
                $profile = $user->profile;
                $entity = $this->getNewEntity($class);
                $entity->owner = $profile;
                $entity->save(true);
                $this->assertPermissionDenied(function() use($entity){
                    $entity->destroy(true);
                }, "Asserting that a $role user CANNOT destroy his own $plural");

            }
        }

        foreach ($this->entities as $class => $plural){
            $this->resetTransactions();
            $user = $this->getUser('superAdmin', 1);
            $this->user = $user;
            $profile = $user->profile;
            $entity = $this->getNewEntity($class);
            $entity->owner = $profile;
            $entity->save(true);
            $this->assertPermissionGranted(function() use($entity){
                $entity->destroy(true);
            }, "Asserting that a Super Admin user CAN destroy his own $plural");

        }

        foreach(['normal', 'staff', 'admin'] as $role){
            foreach ($this->entities as $class => $plural){
                $this->resetTransactions();

                $user = $this->getUser('normal', 0);
                $this->user = $user;
                $profile = $user->profile;
                $entity = $this->getNewEntity($class);
                $entity->owner = $profile;
                $entity->save(true);

                $this->user = $this->getUser($role, 1);

                $this->assertPermissionDenied(function() use($entity){
                    $entity->destroy(true);
                }, "Asserting that a $role user CANNOT destroy other user $plural");

            }
        }

        foreach ($this->entities as $class => $plural){
            $this->resetTransactions();

            $user = $this->getUser('normal', 0);
            $this->user = $user;
            $profile = $user->profile;
            $entity = $this->getNewEntity($class);
            $entity->owner = $profile;
            $entity->save(true);

            $this->user = $this->getUser('superAdmin', 1);

            $this->assertPermissionGranted(function() use($entity){
                $entity->destroy(true);
            }, "Asserting that a Super Admin user CAN destroy other user $plural");

        }
    }
}