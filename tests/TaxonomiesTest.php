<?php
require_once 'bootstrap.php';

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;

class TaxonomiesTests extends MapasCulturais_TestCase{
    protected function _registerTaxonomy(){
        $app = App::i();
        $taxonomies = [
            'test_1' => 'Test Taxonomy 1',
            'test_2' => 'Test Taxonomy 2',
        ];

        $id = 10;

        foreach ($taxonomies as $slug => $description){
            $id++;
            $def = new \MapasCulturais\Definitions\Taxonomy($id, $slug, $description);
            $app->registerTaxonomy('MapasCulturais\Entities\Agent', $def);
        }
    }
    
    function _getAgentById($id){
        $app = App::i();
        $app->em->clear();
        return $app->repo('Agent')->find($id);
    }
    
    function testPersistence(){
        $this->_registerTaxonomy();
        $this->user = 'normal';
        
        $terms_1 = [
            'test_1' => ['test 1', 'test 2', 'test 3'],
            'tag' => ['tag 1', 'tag 2'],
            'not_registered' => ['not 1', 'not 2'] 
        ];
        
        $terms_2 = [
            'test_1' => ['test 2', 'test 3', 'test 4'],
            'test_2' => ['test 21', 'test 22', 'test 23'],
            'tag' => ['tag 2', 'tag 3'],
            'not_registered' => ['not 1', 'not 2'] 
        ];
        
        $terms_3 = [
            'test_1' => ['test 2', 'test 3', 'test 4']
        ];
        
        $agent = $this->getNewEntity('Agent');
        
        $agent->terms = $terms_1;
        $agent->save(true);
        
        $agent = $this->_getAgentById($agent->id);
        
        $this->assertEquals($terms_1['test_1'], $agent->terms['test_1'], 'Asserting that registered taxonomy terms was saved');
        $this->assertEquals($terms_1['tag'], $agent->terms['tag'], 'Asserting that registered taxonomy terms was saved');
        
        $agent->terms = $terms_2;
        $agent->save(true);
        
        $agent = $this->_getAgentById($agent->id);
        
        $this->assertEquals($terms_2['test_1'], $agent->terms['test_1'], 'Asserting that registered taxonomy terms was saved');
        $this->assertEquals($terms_2['tag'], $agent->terms['tag'], 'Asserting that registered taxonomy terms was saved');
        
    }
    
    function testNotRegisteredTaxonomies(){
        $this->_registerTaxonomy();
        $this->user = 'normal';
        
        $app = App::i();
        $conn = $app->em->getConnection();
        
        $terms_1 = [
            'test_1' => ['test 1', 'test 2', 'test 3'],
            'tag' => ['tag 1', 'tag 2'],
            'not_registered' => ['not 1', 'not 2'] 
        ];
        
        $terms_2 = [
            'test_1' => ['test 2', 'test 3', 'test 4'],
            'test_2' => ['test 21', 'test 22', 'test 23'],
            'tag' => ['tag 2', 'tag 3'],
            'not_registered' => ['not 1', 'not 2'] 
        ];
        
        
        $agent = $this->getNewEntity('Agent');
        
        $agent->terms = $terms_1;
        
        $agent->save(true);
        
        $agent = $this->_getAgentById($agent->id);
        
        $this->assertArrayNotHasKey('not_registered', $agent->terms, 'Asserting that not registered taxonomy terms was not saved');
        
        
        $conn->executeQuery("INSERT INTO term(id,taxonomy,term) VALUES (10000,50,'not registered 1')");
        $conn->executeQuery("INSERT INTO term(id,taxonomy,term) VALUES (10001,50,'not registered 2')");
        
        $conn->executeQuery("INSERT INTO term_relation(term_id,object_type,object_id) VALUES (10000,'MapasCulturais\Entities\Agent',{$agent->id})");
        $conn->executeQuery("INSERT INTO term_relation(term_id,object_type,object_id) VALUES (10001,'MapasCulturais\Entities\Agent',{$agent->id})");
        
        $agent = $this->_getAgentById($agent->id);
        $agent->terms = $terms_2;
        $agent->save(true);
        
        $agent = $this->_getAgentById($agent->id);
        $trs = $conn->fetchAll('SELECT * FROM term_relation WHERE term_id IN (10000,10001)');
        
        $this->assertEquals(2,count($trs), 'Asserting that not registered taxonomy terms was not deleted after save terms');
        
    }
}