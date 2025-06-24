<?php
namespace MapasCulturais\Tests;

use Tests\Abstract\TestCase;

class HooksTest extends TestCase {
    function testHookOrder(){
        
        $app = $this->app;
        $result = [];

        $app->hook('test hook order', function() use(&$result){
            $result[] = 4;
        }, 11);

        $app->hook('test hook order', function() use(&$result){
            $result[] = 1;
        }, 10);

        $app->hook('test hook order', function() use(&$result){
            $result[] = 2;
        }, 10);

        $app->hook('test hook order', function() use(&$result){
            $result[] = 3;
        }, 10);

        $app->hook('test hook order', function() use(&$result){
            $result[] = 0;
        }, 9);

        $app->applyHook('test hook order');

        $this->assertCount(5, $result, 'Certificando que o hook executou 5 vezes');

        $this->assertEquals(0, $result[0], 'Certificando que o hook com prioridade 9 foi o primeiro a ser executado mesmo tendo sido agendado por último');
        $this->assertEquals(1, $result[1], 'Certificando que o hook com prioridade 10 foram executados em ordem de enfileiramento');
        $this->assertEquals(2, $result[2], 'Certificando que o hook com prioridade 10 foram executados em ordem de enfileiramento');
        $this->assertEquals(3, $result[3], 'Certificando que o hook com prioridade 10 foram executados em ordem de enfileiramento');
        $this->assertEquals(4, $result[4], 'Certificando que o hook com prioridade 11 foi o último a ser executado mesmo sendo o primeiro a ser enfileirado');
    }

    function testHookWildcard() {
        $app = $this->app;
        
        $hooks = [
            'field_<<*>>',
            '<<projectName|field_*>>',
            'field_<<*>>,projectName'
        ];

        $result = [];
        foreach($hooks as &$hook) {
            $app->hook($hook, function() use($hook, &$result) {
                $result[] = $hook;
            });
        }

        $app->applyHook('field_10');
        $app->applyHook('projectName');

        $expected = [
            'field_<<*>>',
            '<<projectName|field_*>>',
            'field_<<*>>,projectName',
            '<<projectName|field_*>>',
            'field_<<*>>,projectName'
        ];
        $this->assertEquals(implode(':', $result), implode(':', $expected), 'Certificando que os hooks com wildcard estão funcionando corretamente');

    }
}