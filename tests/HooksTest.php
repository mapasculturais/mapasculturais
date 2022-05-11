<?php
require_once __DIR__.'/bootstrap.php';

class HooksTest extends MapasCulturais_TestCase{
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

        $this->assertEquals(0, $result[0]);
        $this->assertEquals(1, $result[1]);
        $this->assertEquals(2, $result[2]);
        $this->assertEquals(3, $result[3]);
        $this->assertEquals(4, $result[4]);
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