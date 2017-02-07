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
//
//    function testFindOneMethod() {
//        $props = 'id,name,shortDescription';
//        
//        $agent1 = $this->app->repo('Agent')->find(1)->simplify($props);
//        $response1 = $this->findOne('agent', [
//            'id=EQ(1)',
//            '@select=' . $props
//        ]);
//        
//        $this->assertEquals($agent1, $response1);
//        
//        $agent2 = $this->app->repo('Agent')->find(2)->simplify($props);
//        $response2 = $this->findOne('agent', [
//            'id=EQ(2)',
//            '@select=' . $props
//        ]);
//        
//        $this->assertEquals($agent2, $response2);
//    }

}
