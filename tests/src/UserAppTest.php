<?php

namespace Tests;

use Apps\Entities\UserApp;
use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class UserAppTest extends Abstract\TestCase
{
    use RequestFactory, 
        UserDirector;
    
    function testUserAppAPI()
    {
        $user1 = $this->userDirector->createUser();

        $this->login($user1);

        $user_app = new UserApp;
        $user_app->name = 'app 1';

        $user_app->save(true);

        $query = new ApiQuery(UserApp::class, [
            '@select' => '*',
            '@order' => 'createTimestamp ASC'
        ]);

        $result = $query->findOne();

        $this->assertNotNull($result, 'Garantindo que a API retorne o UserApp para o usuário proprietário');
        $this->assertEquals($result['publicKey'], $user_app->publicKey, 'Garantindo que a chave retornada está correta');


        $user2 = $this->userDirector->createUser();
        $this->login($user2);

        $query = new ApiQuery(UserApp::class, [
            '@select' => '*',
            '@order' => 'createTimestamp ASC'
        ]);
        $result = $query->findOne();
        
        $this->assertEmpty($result, 'Garantindo que a API NÃO retorna o UserApp para outro usuário');


        $this->logout();
        $query = new ApiQuery(UserApp::class, [
            '@select' => '*',
            '@order' => 'createTimestamp ASC'
        ]);
        $result = $query->findOne();
        $this->assertEmpty($result, 'Garantindo que a API NÃO retorna o UserApp para usuários deslogados');

    }
}