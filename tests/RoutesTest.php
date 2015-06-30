<?php

require_once __DIR__ . '/bootstrap.php';

class RoutesTest extends MapasCulturais_TestCase {
    function assetStatus($method, $status, $url, $message){
        $c = $this->$method($url);
        $this->assertEquals($status, $c->http_status_code, $message);
        return $c;
    }

    function assertGet200($url, $message){
        return $this->assetStatus('get', 200, $url, $message);
    }

    function assertGet401($url, $message){
        return $this->assetStatus('get', 401, $url, $message);
    }

    function assertGet403($url, $message){
        return $this->assetStatus('get', 403, $url, $message);
    }

    function assertGet404($url, $message){
        return $this->assetStatus('get', 404, $url, $message);
    }

    function assertGet503($url, $message){
        return $this->assetStatus('get', 503, $url, $message);
    }

    function testHome(){
        $this->user = null;

        $this->assertGet200('/', 'assert that home status code is 200');
    }

    function testPages(){
        $this->user = null;

        $this->assertGet200('/sobre/', 'assert that about page status code is 200');
        $this->assertGet200('/como-usar/', 'assert that about page status code is 200');
    }

    function testSearch(){
        $this->user = null;

        $this->assertGet200('/busca/', 'assert that about page status code is 200');
    }

    function test404(){
        $this->user = null;

        $this->assertGet404('/' . uniqid('404-'), 'assert that code is 404');
    }

    function testEntitiesRoutesWithGuest(){
        $this->user = null;

        foreach($this->entities as $class => $name){
            $entities = $this->app->repo($class)->findAll();

            foreach($entities as $e){
                $this->assertGet200($e->singleUrl, "assert that the status code of single of {$class} with id {$e->id} is 200 for guest users");
                $this->assertGet403($e->deleteUrl, "assert that the status code of delete url of {$class} with id {$e->id} is 401 for guest users");
                $this->assertGet401($e->editUrl, "assert that the status code of edit url of {$class} with id {$e->id} is 401 for guest users");
            }
        }
    }

    function testEntitiesRoutesWithNormalUser(){
        $user = $this->getUser('normal');
        $this->user = $user;

        foreach($this->entities as $class => $name){
            $entities = $user->$name;

            $this->assertGet200($this->app->createUrl(strtolower($class), 'create'), "assert that the status code of {$class} create is 200");

            foreach($entities as $e){
                $this->assertGet200($e->singleUrl, "assert that the status code of single of my {$class} with id {$e->id} is 200");
                $this->assertGet200($e->editUrl, "assert that the status code of edit url of my {$class} with id {$e->id} is 200");
            }
        }
    }
}
