<?php
require_once 'bootstrap.php';


class UserEmailTest extends MapasCulturais_TestCase{
    function testUserEmail(){
        $app = MapasCulturais\App::i();
        $user = $app->repo('User')->findOneBy(array('id' => 37));

        $this->assertEquals('leandro@hacklab.com.br', $user->email);
    }
}