<?php
namespace Ceara;

use MapasCulturais\App;
use MapasCulturais\Entities;

class AuthProvider extends \MapasCulturais\AuthProviders\OpauthOpenId{

    public function _getAuthenticatedUser() {
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();
            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId('OpenId');
            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);
            
            if(!$user){
                $email = $response['auth']['info']['email'];
                
                $user = $app->repo('User')->findOneBy(['email' => $email]);
                
                if($user){
                    $profile = $user->profile;

                    if(('1' !== (string) $profile->type) && strtolower(trim($profile->name)) != strtolower(trim($name)) && strtolower(trim($profile->nomeCompleto)) != strtolower(trim($name))){
                        // cria um agente do tipo user profile para o usuário criado acima
                        $agent = new Entities\Agent($user);

                        $agent->name = $name;
                        $agent->type = 1;

                        $agent->save(true);

                        $user->profile = $agent;

                        $user->save(true);
                    }
                }
            }

            return $user;

        }else{
            return null;
        }
    }
}