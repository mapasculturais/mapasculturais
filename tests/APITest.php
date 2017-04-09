<?php
require_once __DIR__ . '/bootstrap.php';

class APITest extends MapasCulturais_TestCase {
    
    function api($entity, $_params){
        if(is_string($_params)){
            parse_str($_params,$params);
        } else {
            $params = $_params;
        }
        $rs = new MapasCulturais\ApiQuery("MapasCulturais\Entities\\$entity", $params);
        return $rs;
    }
    
    function apiFind($entity, $_params){
        $rs = $this->api($entity, $_params);
        return $rs->find();
    }
    
    function apiFindOne($entity, $_params){
        $rs = $this->api($entity, $_params);
        return $rs->findOne();
    }
    
    function apiCount($entity, $_params){
        $rs = $this->api($entity, $_params);
        return $rs->count();
    }

    function testFindOneMethod() {
        $simplify = function($entity, $props){
            return (array) $entity->simplify($props);
        };
        
        $cps = [
            'id' => $simplify,
            'id,singleUrl,editUrl,deleteUrl,destroyUrl,endereco,En_CEP' => $simplify,

            'id,name,user.{id,email,profile.{id,name,singleUrl,editUrl,deleteUrl,endereco,En_CEP}}' => function($entity){
                $user = $entity->ownerUser;
                $profile = $user->profile;
                return [
                    'id' => $entity->id,
                    'name' => $entity->name,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'profile' => [
                            'id' => $profile->id,
                            'name' => $profile->name,
                            'singleUrl' => $profile->singleUrl,
                            'editUrl' => $profile->editUrl,
                            'deleteUrl' => $profile->deleteUrl,
                            'endereco' => $profile->endereco,
                            'En_CEP' => $profile->En_CEP
                        ]
                    ]
                ];
            },
            'id,name,owner' => function($entity){
                $user = $entity->ownerUser;
                $profile = $user->profile;
                return [
                    'id' => $entity->id,
                    'name' => $entity->name,
                    'owner' => $entity->owner->id
                ];
            },
        ];
        
        foreach([null,'superAdmin','admin','normal'] as $user){
            $this->user = $user;
            
            // agente nao tem owner, tem parent...
            foreach(['Space', 'Project', 'Event'] as $class){
                $entities = $this->app->repo($class)->findAll();
                
                foreach($entities as $entity){
                    if($entity->status < 1) continue;
                    
                    foreach($cps as $props => $proc){
                        $response = $this->apiFindOne($class, "@select={$props}&id=EQ({$entity->id})");
                        $_entity = $proc($entity, $props);
                        
                        $this->assertEquals($_entity, $response, "asserting {$class} api findOne to \$user");
                    }
                }
            }
        }
    }
            
    function testQuerySintax(){
        $this->user = 'admin';
        
        $s1 = 'id,name,user.id,user.email,user.profile.id,user.profile.name,user.profile.singleUrl,user.profile.endereco,user.profile.spaces.id,user.profile.spaces.name,user.profile.spaces.singleUrl,user.profile.spaces.endereco';
        $s2 = 'id,name,user.{id,email,profile.id,profile.name,profile.singleUrl,profile.endereco,profile.spaces.id,profile.spaces.name,profile.spaces.singleUrl,profile.spaces.endereco}';
        $s3 = 'id,name,user.{id,email,profile.{id,name,singleUrl,endereco,spaces.id,spaces.name,spaces.singleUrl,spaces.endereco}}';
        $s4 = 'id,name,user.{id,email,profile.{id,name,singleUrl,endereco,spaces.{id,name,singleUrl,endereco}}}';
        
        $entities = $this->app->repo('Event')->findAll();
        foreach($entities as $entity){
            $r1 = $this->apiFind('event', "id=EQ($entity->id)&@select=$s1");
            $r2 = $this->apiFind('event', "id=EQ($entity->id)&@select=$s2");
            $r3 = $this->apiFind('event', "id=EQ($entity->id)&@select=$s3");
            $r4 = $this->apiFind('event', "id=EQ($entity->id)&@select=$s4");
            
            $this->assertEquals($r1, $r2, 'asserting same result for different sitaxes');
            $this->assertEquals($r1, $r3, 'asserting same result for different sitaxes');
            $this->assertEquals($r1, $r4, 'asserting same result for different sitaxes');
        }
    }
    
    function testJsonOutput(){
        $event_id = 522;
        
        $s1 = 'id,name,user.{id,email,profile.{id,name,singleUrl,endereco,spaces.{id,name,singleUrl,endereco}}}';
        
        $query = "id=EQ($event_id)&@select=$s1";
        
        $r1 = json_decode(json_encode($this->apiFind('event', $query)));
        
        $curl = $this->get("/api/event/find?$query");
        $r2 = json_decode($curl->response);
        
        $this->assertEquals($r1, $r2);

    }
    
    function testEventsOfProject(){
        $this->resetTransactions();
        // assert that users WITHOUT control of a project CANNOT create events to this project
        $user1 = $this->getUser('normal', 0);
        $user2 = $this->getUser('normal', 1);

        $project = $user2->projects[0];

        $project->createAgentRelation($user1->profile, "AGENTS WITH CONTROL", true, true);

        $this->user = $user1;

        // assert that users with control of a project CAN view draft events related to this project
        $event_name = uniqid('EVENT:');
                
        $evt = $this->getNewEntity('Event');
        $evt->name = $event_name;
        $evt->project = $project;
        $evt->status = \MapasCulturais\Entities\Event::STATUS_DRAFT;
        $evt->save();
        
        $this->user = $user2;
        
        $this->app->recreatePermissionsCacheOfListedEntities();
        
        $r1 = $this->apiFind('event', "project=EQ({$project->id})&@select=id,status,name,project.{id,name}&@permissions=view&status=GTE(0)");
        
        $this->assertCount(1, $r1, 'assert that one result was returned');
        
        $this->assertEquals([
            'id' => $evt->id,
            'name' => $evt->name,
            'status' => \MapasCulturais\Entities\Event::STATUS_DRAFT,
            'project' => [
                'id' => $project->id,
                'name' => $project->name
            ]
        ], $r1[0], 'verify the returned event');
    }
}
