<?php
/*
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
        $agent->name = 'Nameless';
        $agent->save(true);

        $agentFound = $app->repo('Agent')->findOneBy(array('id'=>$agent->id));

        $this->assertEquals($agentFound->name, 'Nameless');
        $this->assertEquals($agentFound->user->id, $user->id);


        $space = new Space;
        $space->name = 'Test Space 123';
        $space->shortDescription = 'Test Space 123 Description';
        $space->type = 10;
        $space->ownerId = $user->profile->id;
        $space->save(true);


        $event = new Event;
        $event->name = 'Test 123 Event';
        $event->shortDescription = 'Test 123 Event Description';
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

    function testURLs(){
        $app = \MapasCulturais\App::i();
        $urls = [
            'api/agent/find/?&@select=id,name,location&@order=name%20ASC',
            'api/space/find/?&@select=id,name,location&@order=name%20ASC',

            'api/space/find/?&_geoLocation=GEONEAR(-46.65618896484375,-23.619361679019544,12782)&@select=id,name,location&@order=name%20ASC',

            'api/space/findByEvents/?&@select=id,name,location&@order=name%20ASC',

            'api/agent/find/?&@select=id,singleUrl,name,type,shortDescription,terms&@files=(avatar.avatarBig):url&@page=1&@limit=10&@order=name%20ASC',
            'api/space/find/?&@select=id,singleUrl,name,type,shortDescription,terms&@files=(avatar.avatarBig):url&@page=1&@limit=10&@order=name%20ASC',
            'api/event/findByLocation/?&@select=id,singleUrl,name,type,shortDescription,terms,classificacaoEtaria&@files=(avatar.avatarBig):url&@page=1&@limit=10&@order=name%20ASC',

            'api/project/find/?&@select=id,singleUrl,name,type,shortDescription,terms,registrationFrom,registrationTo&@files=(avatar.avatarBig):url&@page=1&@limit=10&@order=name%20ASC'
        ];

        foreach($urls as $url){
            $this->assertNotEmpty(file_get_contents($app->config['site.url'].$url));
        }
    }
}
*/