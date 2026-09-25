<?php

namespace DeleteAccount;

use MapasCulturais\App;
use MapasCulturais\i;

/**
 * Módulo legado de exclusão direta de conta.
 *
 * O fluxo de exclusão passou a ser feito via solicitação LGPD em
 * Conta e Privacidade (UserManagement). Este módulo mantém apenas
 * o metadado do token por compatibilidade e bloqueia a exclusão direta.
 */
class Module extends \MapasCulturais\Module {

    public function _init() {
        $app = App::i();

        $app->hook('POST(user.deleteAccount)', function () {
            /** @var \MapasCulturais\Controllers\User $this */
            $this->requireAuthentication();
            $this->errorJson(
                i::__('A exclusão de conta deve ser solicitada em Conta e Privacidade.'),
                400
            );
        });

        $app->hook('GET(panel.deleteAccount)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();
            $app->redirect($app->createUrl('panel', 'my-account'));
        });
    }

    public function register() {
        $this->registerUserMetadata('deleteAccountToken', [
            'label' => 'Delete Account Token',
            'private' => true,
        ]);
    }
}
