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
    
    function assertApiResult($controller, $endpoint, $query, $expected){
        $curl = $this->get("/api/$controller/$endpoint", $query);
        $this->assertEquals($expected, json_decode($curl->response), "asserting that $controller $endpoint response is valid");
        
    }
    
    function testAPI(){
        $queries = json_decode(file_get_contents(__DIR__ . '/api-queries.json'));
        foreach($queries as $query){
            $this->app->cache->deleteAll();
            
            if($query->userId){
                $this->user = $query->userId;
            }else{
                $this->user = null;
            }
            
            $controller = $query->controller;
            $endpoint = $query->endpoint;
            $qdata = $query->qdata;
            $expected = $query->result;
            
            $this->assertApiResult($controller, $endpoint, $qdata, $expected);
        }
    }
    
}
