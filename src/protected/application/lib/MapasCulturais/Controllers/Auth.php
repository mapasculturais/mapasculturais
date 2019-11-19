<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;
use \MapasCulturais\i;

class Auth extends \MapasCulturais\Controller{
    function ALL_logout(){
        $app = App::i();
        $app->auth->logout();
        $app->redirect($app->baseUrl);
    }
    
    function GET_login(){
        $app = App::i();
        
        if(isset($this->getData['redirectTo'])){
            $app->auth->requireAuthentication($this->getData['redirectTo']);
        }else{
            $app->auth->requireAuthentication();
        }
    }

    /**
     * Authorize Public Key to 
     *
     * @return void
     */
    function GET_getProcuration(){
        $this->requireAuthentication();

        $app = App::i();

        $allowed_permissions = ['manageEventAttendance'];

        if(!isset($this->data['attorney'])){
            $this->errorJson(i::__('Parâmetro attorney não informado'));
        }

        if(!isset($this->data['permission'])){
            $this->errorJson(i::__('Parâmetro permission não informado'));
        } 

        $user = null;

        if(is_numeric($this->data['attorney'])){
            $user = $app->repo('User')->find($this->data['attorney']);
        } else {
            $user_app = $app->repo('UserApp')->find($this->data['attorney']);
            if($user_app){
                $user = $user_app->user;
            }
        }

        if(!$user){
            $this->errorJson(i::__('Usuário procurador não encontrado'));
        }

        if(false) $user = new \MapasCulturais\Entities\User;

        if(isset($this->data['until'])){
            try{
                $until = new \DateTime($this->data['until']);
            } catch(\Exception $e){
                $this->errorJson(i::__('Formato do parâmetro until inválido'));
            }
        } else {
            $until = null;
        }

        $procuration = $user->makeAttorney($this->data['permission'], $until);

        $this->render('procuration', ['procuration' => $procuration]);
    }
}