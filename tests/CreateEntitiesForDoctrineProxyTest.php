<?php
require_once __DIR__.'/bootstrap.php';

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\EventOccurrence;
use MapasCulturais\Entities\EventOccurrenceRecurrence;


class CreateEntitiesForDoctrineProxyTest extends MapasCulturais_TestCase{

    function testAll() {

        $app = \MapasCulturais\App::i();

        $user = new User;
        $user->authUid = 'fakes';
        $user->email = 'user@doamain.com';
        $user->save(true);

        $userFound = $app->repo('User')->findOneBy(array('email'=>'user@doamain.com'));

        $this->assertEquals($userFound->email, 'user@doamain.com');
        $this->assertEquals($userFound->authUid, 'fakes');

        $agent = new Agent($user);
        $agent->isUserProfile = true;
        $agent->name = 'Namealess';
        $agent->save(true);

        $agentFound = $app->repo('Agent')->findOneBy(array('id'=>$agent->id));

        $this->assertEquals($agentFound->name, 'Namealess');
        $this->assertEquals($agentFound->user->id, $user->id);


        $space = new Space;
        $space->name = 'Test Space 123';
        $space->shortDescription = 'Test Space 123 Description';
        $space->type = 10;
        $space->ownerId = $user->profile->id;
        $space->save(true);


        $event = new Event;
        $event->name = 'Test Event 123';
        $event->shortDescription = 'Test Event 123 Description';
        $event->ownerId = $user->profile->id;
        $event->save(true);

        $project = new Project;
        $project->name = "Test1";
        $project->type = 1;

        $project->save(true);

        $event->setOwnerId($agent->id);
        $event->setProjectId($project->id);
        $event->save(true);

        $eventFound = $app->repo('Event')->findOneBy(array('id'=>$event->id));

        $this->assertEquals($eventFound->project->id, $project->id);

    }

}
