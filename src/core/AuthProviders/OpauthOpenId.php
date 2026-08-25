<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\i;

/**
 * SHIM DE DEPRECAÇÃO — OpenID 2.0 (protocolo descontinuado).
 *
 * O protocolo OpenID 2.0 está descontinuado e o endpoint default do driver
 * legado (Google o8) foi desativado em 2015; nenhuma biblioteca do motor novo
 * cobre o protocolo. Este driver permanece instanciável para não quebrar
 * configs existentes ('auth.provider' => 'OpauthOpenId'), mas o fluxo de
 * autenticação SEMPRE falha de forma graciosa com mensagem amigável e log de
 * deprecação. A identidade histórica (auth_provider=1 'OpenID') permanece
 * registrada e preservada no banco.
 *
 * Migração: utilize um provedor OIDC (ver UPGRADING.md e docs/plugins/auth/).
 */
class OpauthOpenId extends \MapasCulturais\AuthProvider{

    /**
     * Inicializa o shim: registra as rotas com comportamento de deprecação.
     *
     * @return void
     */
    protected function _init() {
        $app = App::i();

        $app->log->warning('[auth] OpauthOpenId está DEPRECADO: OpenID 2.0 é um protocolo descontinuado e não possui motor na linha 8.x. Migre para um provedor OIDC (ver UPGRADING.md).');

        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $app->redirect($app->createUrl('site', 'index'));
        });

        $app->hook('<<GET|POST>>(auth.openid)', function () use($app){
            $app->log->warning('[auth] Tentativa de login via OpenID 2.0 (deprecado) — fluxo encerrado.');
            $app->redirect($app->createUrl('site', 'index'));
        });

        $app->hook('GET(auth.response)', function () use($app){
            $app->auth->processResponse();
            $app->redirect($app->controller('auth')->createUrl(''));
        });
    }

    /**
     * Limpa a sessão do usuário
     *
     * @return void
     */
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
    }

    /**
     * Requer autenticação do usuário
     *
     * @return void
     */
    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, i::__('This action requires authentication'));
        }else{
            $this->_setRedirectPath($app->request->getPathInfo());
            $app->redirect($app->controller('auth')->createUrl(''), 401);
        }
    }

    /**
     * Retorna a URL para redirecionamento após autenticação
     *
     * @return string
     */
    public function getRedirectPath(){
        $path = key_exists('mapasculturais.auth.redirect_path', $_SESSION) ?
                    $_SESSION['mapasculturais.auth.redirect_path'] : App::i()->createUrl('site','');

        unset($_SESSION['mapasculturais.auth.redirect_path']);

        return $path;
    }

    /**
     * Sempre null: o protocolo não possui motor.
     *
     * @return null
     */
    public function _getAuthenticatedUser() {
        return null;
    }

    /**
     * Sempre falha gracioso (auth.failed) — nunca autentica.
     *
     * @return boolean
     */
    public function processResponse(){
        App::i()->applyHook('auth.failed');
        return false;
    }

    /**
     * Nunca alcançado (processResponse sempre falha antes).
     */
    protected function _createUser($data) {
        return null;
    }
}
