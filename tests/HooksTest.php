<?php
require_once __DIR__.'/bootstrap.php';

class HooksTest extends MapasCulturais_TestCase{
    function testHookOrder(){
        $app = $this->app;
        $result = [];

        $app->hook('test hook order', function() use(&$result){
            $result[] = 1;
        });

        $app->hook('test hook order', function() use(&$result){
            $result[] = 2;
        });

        $app->hook('test hook order', function() use(&$result){
            $result[] = 3;
        });

        $app->applyHook('test hook order');

        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(3, $result[2]);
    }
}