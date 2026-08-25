<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

/**
 * Driver de autenticação local-only.
 *
 * Para instâncias que usam EXCLUSIVAMENTE login local (e-mail/CPF + senha) sem
 * provedor social: aponte 'auth.provider' => 'Local' e habilite o módulo
 * LocalAuth (AUTH_LOCAL_LOGIN_ENABLED=true, default). As rotas de login vivem
 * no módulo; este driver apenas satisfaz o contrato AuthProvider,
 * resolvendo a sessão gravada pelo módulo (chave própria, distinta da do
 * plugin MultipleLocalAuth — coexistência por chaves de sessão distintas).
 *
 * Usuários locais mantêm auth_provider=0 (o nome 'local' NÃO é registrado no
 * mapa de providers do core — invariante de identidade do login local).
 */
class Local extends \MapasCulturais\AuthProvider{

    protected function _init() {
        // Rotas/UI pertencem ao módulo LocalAuth; nada a registrar aqui.
    }

    public function _cleanUserSession() {
        unset($_SESSION[\LocalAuth\Module::SESSION_KEY]);
    }

    public function _getAuthenticatedUser() {
        // A resolução da sessão local acontece na base (AuthProvider) para todos
        // os drivers; este método nunca autentica por callback (não há fluxo
        // externo em um provider local).
        return null;
    }

    protected function _createUser($data) {
        // Criação de usuários locais é responsabilidade do módulo (register).
        throw new \RuntimeException('Local driver: user creation belongs to the LocalAuth module (POST /autenticacao/register).');
    }

    public function _requireAuthentication() {
        $app = App::i();
        if ($app->request->isAjax()) {
            $app->halt(401, \MapasCulturais\i::__('É preciso estar autenticado para realizar esta ação'));
        } else {
            $this->_setRedirectPath($app->request->getPathInfo());
            $app->redirect($app->controller('auth')->createUrl(''), 302);
        }
    }
}
