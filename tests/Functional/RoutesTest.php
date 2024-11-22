<?php

class RoutesTest extends \MapasCulturais\Tests\TestCase {

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
        $this->markTestSkipped('Maybe something with assetGet404');
        $this->user = null;

        $this->assertGet404('/' . uniqid('404-'), 'assert that code is 404');
    }

    function testEntitiesRoutesWithGuest(){
        $this->markTestSkipped('No reason to fail, need more research');
        $this->user = null;

        foreach($this->entities as $class => $name){
            $entities = $this->app->repo($class)->findAll();

            $e = $entities[0];
            // foreach($entities as $e){
                $this->assertGet200($e->singleUrl, "assert that the status code of single of {$class} with id {$e->id} is 200 for guest users");
                $this->assertGet403($e->deleteUrl, "assert that the status code of delete url of {$class} with id {$e->id} is 401 for guest users");
                $this->assertGet401($e->editUrl, "assert that the status code of edit url of {$class} with id {$e->id} is 401 for guest users");
            // }
        }
    }

    function testEntitiesRoutesWithNormalUser(){
        $user = $this->getUser('normal');
        $this->user = $user;

        foreach($this->entities as $class => $name){
            $entities = $user->$name;

            $e = $entities[0];
            // foreach($entities as $e){
                $this->assertGet200($e->singleUrl, "assert that the status code of single of my {$class} with id {$e->id} is 200");
                $this->assertGet200($e->editUrl, "assert that the status code of edit url of my {$class} with id {$e->id} is 200");
            // }
        }
    }
    
}
