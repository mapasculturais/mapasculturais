<?php

require_once __DIR__ . '/bootstrap.php';

class APITest extends MapasCulturais_TestCase {
    
    function parseQuery($query){
        $nq = [];
        
        foreach($query as $p){
            list($key, $val) = explode('=', $p);
            $nq[$key] = $val;
        }
        
        return $nq;
    }
    
    function findOne($controller, $query){
        $curl = $this->get('/api/' . $controller . '/findOne', $this->parseQuery($query));
        
        return json_decode($curl->response);
    }

    function testFindOneMethod() {
        $props = 'id,name,shortDescription';
        
        $agent1 = $this->app->repo('Agent')->find(1)->simplify($props);
        $response1 = $this->findOne('agent', [
            'id=EQ(1)',
            '@select=' . $props
        ]);
        
        $this->assertEquals($agent1, $response1);
        
        $agent2 = $this->app->repo('Agent')->find(2)->simplify($props);
        $response2 = $this->findOne('agent', [
            'id=EQ(2)',
            '@select=' . $props
        ]);
        
        $this->assertEquals($agent2, $response2);
    }

    function test404(){
        $curl = $this->get('/foo/bar');
        
        $this->assertEquals(404, $curl->error_code);
    }
}
