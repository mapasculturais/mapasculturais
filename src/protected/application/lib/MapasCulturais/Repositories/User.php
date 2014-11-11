<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\Entities;

class User extends \MapasCulturais\Repository{

    /**
     * Retorna um usuário pelo auth_uid e auth_provider
     * @param string $auth_uid
     * @param int $auth_provider
     * @return \MapasCulturais\Entities\User|null
     */    
    public function getByAuth($auth_provider, $auth_uid){
        $user_query = $this->_em->createQuery('SELECT u FROM MapasCulturais\Entities\User u WHERE u.authProvider=:auth_provider AND u.authUid = :auth_uid');

        $user_query->setParameter('auth_provider', $auth_provider);
        $user_query->setParameter('auth_uid', $auth_uid);
        $user = $user_query->getOneOrNullResult();
        return $user;
    }

    public function createByAuthResponse($response){
        $app = \MapasCulturais\App::i();
        
        $app->disableAccessControl();
    
        // cria o usuário
        $user = new Entities\User;
        $user->authProvider = $response['auth']['provider'];
        $user->authUid = $response['auth']['uid'];
        $user->email = $response['auth']['info']['email'];
        $this->_em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);
        
        if(isset($response['auth']['info']['name']))
            $agent->name = $response['auth']['info']['name'];
        elseif(isset($response['auth']['info']['first_name']) && isset($response['auth']['info']['last_name']))
            $agent->name = $response['auth']['info']['first_name'] . ' ' . $response['auth']['info']['last_name'];
        else
            $agent->name = 'Sem nome';


        $this->_em->persist($agent);
        $this->_em->flush();
        
        $user->profile = $agent;
        $user->save(true);
        
        $app->enableAccessControl();

        return $user;
    }
}
