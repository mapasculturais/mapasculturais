<?php
require_once 'bootstrap.php';

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;

class MetadataTests extends MapasCulturais_TestCase{
    function testValidations(){
        $app = App::i();
        $type = new MapasCulturais\Definitions\EntityType('MapasCulturais\Entities\Agent', 9999, 'test type');
        $app->registerEntityType($type);

        $metas = array(
            'metaRequired' => array(
                'label' => 'Meta Required',
                'validations' => array(
                    'required' => 'required error message'
                ),
                'validValues' => array('Value','Loren'),
                'invalidValues' => array(null, '')
            ),

            'metaEmail' => array(
                'label' => 'Meta Email',
                'validations' => array(
                    'v::email()' => 'error message'
                ),
                'validValues' => array(null, '', 'test@testing.com', 'testing_123@test.com.br', 'testing.123@test.com.br'),
                'invalidValues' => array('test', 'test', 'teste#teste.com', 'alow as@teste.com')
            ),

            'metaRequiredEmail' => array(
                'label' => 'Meta Email',
                'validations' => array(
                    'required' => 'required error message',
                    'v::email()' => 'error message'
                ),
                'validValues' => array('test@testing.com', 'testing_123@test.com.br', 'testing.123@test.com.br'),
                'invalidValues' => array(null, '', 'test', 'test', 'teste#teste.com', 'alow as@teste.com')
            ),

            'metaUrl' => array(
                'label' => 'Meta Url',
                'validations' => array(
                    'v::url()' => 'error message'
                ),
                'validValues' => array(null, '', 'http://www.test.com', 'http://www.testing-tests.com', 'http://www.test.com/', 'https://www.test.com/', 'http://www.test.com/ok', 'https://testing.com/ok.test', 'http://test.com/ok.php', 'http://a.very.long.domain.name.com/ok.jpg?with=get%20params', 'https://www.test.com/ok' ),
                'invalidValues' => array('htts:///222 asd.com', 'asdasd', 'www.test.com')
            ),

            'metaRequiredUrl' => array(
                'label' => 'Meta Url',
                'validations' => array(
                    'required' => 'required error message',
                    'v::url()' => 'error message'
                ),
                'validValues' => array('http://www.test.com', 'http://www.testing-tests.com', 'http://www.test.com/', 'https://www.test.com/', 'http://www.test.com/ok', 'https://testing.com/ok.test', 'http://test.com/ok.php', 'http://a.very.long.domain.name.com/ok.jpg?with=get%20params', 'https://www.test.com/ok' ),
                'invalidValues' => array(null, '', 'htts:///222 asd.com', 'asdasd', 'www.test.com')
            ),

            'metaContact' => array(
                'label' => 'Meta Contact',
                'validations' => array(
                    'required' => 'required error message',
                    'v::oneOf(v::email(), v::brPhone())' => 'error message'
                ),
                'validValues' => array('test@testing.com', 'testing_123@test.com.br', 'testing.123@test.com.br', '(11) 9876-5432', '(11) 3456-1234', '(11)34561234', '(11) 93456-1234'),
                'invalidValues' => array(null, '', 'test@', 'test', 'teste#teste.com', 'alow as@teste.com', '(11)123', '(1) 123123123123-123')
            )
        );

        foreach($metas as $meta_key => $config){
            $definition = new MapasCulturais\Definitions\Metadata($meta_key, $config);
            $app->unregisterEntityMetadata('MapasCulturais\Entities\Agent', $meta_key);
            $app->registerMetadata($definition, 'MapasCulturais\Entities\Agent', $type->id);

            $agent = new Agent;
            $agent->name = 'Teste';
            $agent->type = $type;

            $agent->terms['area'][] = 'Cinema';

            for($i = 1; $i <= 2; $i++){

                foreach($config['validValues'] as $val){
                    $agent->$meta_key = $val;
                    $errors = $agent->getValidationErrors();
                    $this->assertArrayNotHasKey($meta_key, $errors, print_r(array('type' => "assertArrayNotHasKey", 'KEY' => $meta_key, 'VALID VALUE' => $val, 'errors' => $errors), true));
                }

                foreach($config['invalidValues'] as $val){
                    $agent->$meta_key = $val;
                    $errors = $agent->getValidationErrors();
                    $this->assertArrayHasKey($meta_key, $errors, print_r(array('type' => "assertArrayNotHasKey", 'KEY' => $meta_key, 'INVALID VALUE' => $val, 'errors' => $errors), true));
                }
            }

        }

    }
    
    function testGetMetadata(){
        // testa se o getter funciona para todos os roles

        // guest user
        $this->app->em->clear();
        $this->user = null;
        $event = $this->app->repo('Event')->find(522);
        $this->assertEquals('Livre', $event->classificacaoEtaria);
        
        // normal user
        $this->app->em->clear();
        $this->user = 'normal';
        $event = $this->app->repo('Event')->find(522);
        $this->assertEquals('Livre', $event->classificacaoEtaria);
        
        // admin user
        $this->app->em->clear();
        $this->user = 'admin';
        $event = $this->app->repo('Event')->find(522);
        $this->assertEquals('Livre', $event->classificacaoEtaria);
        
    }
}