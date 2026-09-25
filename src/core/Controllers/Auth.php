<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;
use \MapasCulturais\i;

/**
 * Controlador de Autenticação
 *
 * Este controlador gerencia as operações de autenticação e autorização no sistema Mapas Culturais.
 * Ele fornece endpoints para login, logout e gerenciamento de procurações (autorizações temporárias).
 *
 * @property-read \MapasCulturais\AuthProvider $auth Provedor de autenticação
 * @property-read \MapasCulturais\Entities\User $user Usuário autenticado
 * 
 * @package MapasCulturais\Controllers
 */
class Auth extends \MapasCulturais\Controller {
    /**
     * Construtor do controlador de autenticação
     *
     * Configura hooks para redirecionamento após autenticação.
     */
    function __construct()
    {
        $app = App::i();
        
        $app->hook('GET(auth.index)', function () use($app){
            if(isset($this->data['redirectTo'])){
                $app->auth->setRedirectPath($this->data['redirectTo']);
            }
        },-10);
    }

    /**
     * Realiza logout do usuário atual
     *
     * Esta ação invalida a sessão do usuário e redireciona para a página inicial.
     * 
     * @api {ALL} /auth/logout Logout do usuário
     * @apiDescription Realiza logout do usuário atual
     * @apiGroup AUTH
     * @apiName logout
     * 
     * @return void
     */
    function ALL_logout(){
        $app = App::i();
        $app->auth->logout();
        $app->redirect($app->baseUrl);
    }
    
    /**
     * Realiza login do usuário
     *
     * Esta ação requer autenticação e pode redirecionar para uma URL específica após o login.
     * 
     * @api {GET} /auth/login Login do usuário
     * @apiDescription Realiza login do usuário
     * @apiGroup AUTH
     * @apiName login
     * @apiParam {String} [redirectTo] URL para redirecionar após login bem-sucedido
     * 
     * @return void
     */
    function GET_login(){
        $app = App::i();
        if(isset($this->data['redirectTo'])){
            $app->auth->requireAuthentication($this->data['redirectTo']);
        }else{
            $app->auth->requireAuthentication();
        }
    }

    /**
     * Gera uma procuração (autorização temporária) para um usuário
     *
     * Esta ação permite que um usuário autorize outro usuário (procurador) a realizar
     * ações específicas em seu nome por um período limitado.
     * 
     * @api {GET} /auth/getProcuration Gerar procuração
     * @apiDescription Gera uma autorização temporária (procuração) para outro usuário
     * @apiGroup AUTH
     * @apiName getProcuration
     * @apiPermission user
     * @apiParam {String|Number} attorney Identificador do usuário procurador (ID ou chave de app)
     * @apiParam {String} permission Permissão a ser concedida (ex: 'manageEventAttendance')
     * @apiParam {String} [until] Data limite da procuração (formato ISO 8601)
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
            $user_app = $app->repo(\Apps\Entities\UserApp::class)->find($this->data['attorney']);
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