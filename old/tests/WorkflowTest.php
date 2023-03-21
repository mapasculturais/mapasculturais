<?php
require_once 'bootstrap.php';

class WorkflowTest extends MapasCulturais_TestCase{

    function testEnableAndDisableWorkflow(){
        $this->assertTrue($this->app->isWorkflowEnabled(), 'Asserting that workflow is enabled by default.');

        $this->app->disableWorkflow();
        $this->assertFalse($this->app->isWorkflowEnabled(), 'Asserting that App::disableWorkflow() works.');

        $this->app->enableWorkflow();
        $this->assertTrue($this->app->isWorkflowEnabled(), 'Asserting that App::enableWorkflow() works.');

    }

    function testAuthorityRequestCreation(){
        $this->app->enableWorkflow();

        // asserting that the request authority is created when user tries to TAKE ownership of an entity
        foreach($this->entities as $class => $e){
            $this->resetTransactions();

            $user1 = $this->getUser('normal',0);
            $user2 = $this->getUser('normal',1);

            $entities = $user1->$e;
            $entity = $entities[0];

            $this->assertAuthorizationRequestCreated(function() use($user2, $entity){
                $this->user = $user2;
                $entity->owner = $user2->profile;
                $entity->save();
            }, "Asserting that AuthorityRequest is created when an user tries to take ownership of a {$class}.");
        }

        // asserting that the request authority is created when user tries to TAKE ownership of an entity to another user agent that he has control
        foreach($this->entities as $class => $e){
            $this->resetTransactions();
            
            
            $admin = $this->getUser('admin');
            $this->user = $admin;
            
            $newAgent = $this->getNewEntity('Agent', $admin);
            $newAgent->owner = $admin->profile;
            $newAgent->save(true);
            
            $user1 = $this->getUser('normal',0);
            $user2 = $this->getUser('normal',1);
            
            $newAgent->createAgentRelation($user2->profile, 'CONTROL', true, true, true);
            
            $this->user = $user2;
            
            $this->app->em->refresh($newAgent);
            
            $entities = $user1->$e;
            $entity = $entities[0];
            
            
            $this->assertAuthorizationRequestCreated(function() use($newAgent, $entity){
                $entity->owner = $newAgent;
                $entity->save();
            }, "Asserting that AuthorityRequest is created when an user tries to take ownership of a {$class}. to another user agent that he has control");
            
        }

        // asserting that the request authority is created when user tries to GIVE ownership of an entity
        foreach($this->entities as $class => $e){
            $this->resetTransactions();

            $user1 = $this->getUser('normal',0);
            $user2 = $this->getUser('normal',1);

            $entities = $user1->$e;
            $entity = $entities[0];

            $this->assertAuthorizationRequestCreated(function() use($user1, $user2, $entity){
                $this->user = $user1;
                $entity->owner = $user2->profile;
                $entity->save();
            }, "Asserting that AuthorityRequest is created when an user tries to give ownership of a {$class}.");
        }

        // asserting that a user CANNOT create authority requests to another user
        foreach($this->entities as $class => $e){
            $this->resetTransactions();

            $user1 = $this->getUser('admin',0);
            $user2 = $this->getUser('normal',1);
            $user3 = $this->getUser('normal',0);

            $entities = $user1->$e;
            $entity = $entities[0];

            $this->assertPermissionDenied(function() use($user2, $user3, $entity){
                $this->user = $user3;

                $entity->owner = $user2->profile;

                $entity->save();
            }, "Asserting that an user CANNOT give ownership of a {$class} to another user.");
        }
    }

    function testAuthorityRequestAprove(){
        $this->app->enableWorkflow();

        // asserting that authority workflow works
        foreach($this->entities as $class => $e){
            $this->resetTransactions();

            $user1 = $this->getUser('normal',0);
            $user2 = $this->getUser('normal',1);

            $this->user = $user1;

            $entity = $this->getNewEntity($class);
            $entity->owner = $user1->profile;

            $entity->save(true);

            $request = null;

            // create the request
            try{
                $entity->owner = $user2->profile;
                $entity->save();

            } catch (MapasCulturais\Exceptions\WorkflowRequest $e) {
                $request = $e->requests[0];
            }



            $this->assertInstanceOf('MapasCulturais\Entities\RequestChangeOwnership', $request, "asserting that the request was created");

            $this->assertEquals ($user1->id, $entity->ownerUser->id, "Asserting that BEFORE the request is approved, de owner was NOT changed");

            $this->user = $user2;

            $this->assertFalse($entity->canUser('remove'), "Asserting that the user that will receive the $class CANNOT remove it BEFORE the request is approved");
            $this->assertFalse($entity->canUser('modify'), "Asserting that the user that will receive the $class CANNOT modify it BEFORE the request is approved");

            $this->assertPermissionGranted(function() use($request){

                $request->approve();
            }, 'Asserting that the user that was requested CAN approve the request');

            $this->assertEquals($user2->id, $entity->ownerUser->id, "Asserting that AFTER the request is approved, the $class owner was CHANGED");

            $this->assertTrue($entity->canUser('remove'), "Asserting that the user that received the $class CAN remove it");
            $this->assertTrue($entity->canUser('remove'), "Asserting that the user that received the $class CAN modify it");

        }
    }

    //Verifies Requests Actions:

    //REQUEST TYPE: RequestChangeOwnership


    //Entity Owner: Always An Agent

    //ORIGIN: An Entity
    //DESTINATION: An Agent

    // RequestChangeOwnership - Request
    //  Who can Create this Request: users that control the DESTINATION agent
    //  Who can Approve: users that control the ORIGIN entity owner agent
    //  Who can Reject: users that can create or approve the request

//     function testRequestChangeOwnershipRequest(){
//         $this->app->enableWorkflow();
//     }

    // RequestChangeOwnership - Give
    //  Who can Create this Request: users that control the ORIGIN entity owner agent (@control)
    //  Who can Approve: users that control the DESTINATION agent
    //  Who can Reject: users that can create or approve the request

    // function testRequestChangeOwnershipGive(){
    //    $this->app->enableWorkflow();
    // }


    //REQUEST TYPE: RequestChildEntity

    //ORIGIN: A child Space or a child Project
    //DESTINATION: A parent Space or a parent Project

    //Who can Create this Request: users that control the ORIGIN Entity (child space or project)
    //Who can Approve: users that control the DESTINATION entity (parent space or project)
    //Who can Reject: users that can create or approve the request

    // function testRequestChildEntity(){
    //    $this->app->enableWorkflow();
    // }

    //REQUEST TYPE: RequestAgentRelation

    //ORIGIN: An Entity
    //DESTINATION: An Agent

    //Who can Create this Request: users that control the ORIGIN Entity
    //Who can Approve: users that control the DESTINATION Agent
    //Who can Reject: users that can create or approve the request

    // function testRequestAgentRelation(){
    //    $this->app->enableWorkflow();
    // }

    

//    RequestEventOccurrence,

//    RequestEventProject
}
