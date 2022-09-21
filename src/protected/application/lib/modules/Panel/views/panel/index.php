<?php
use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card');
?>

<div class="panel-home">

    <header class="panel-home__header">
        <div class="panel-home__header--title">
            <label class="title"> <?= i::_e('Painel de controle') ?> </label>
        </div>

        <div class="panel-home__header--user">
            <div class="panel-home__header--user-profile">
                <div class="avatar">
                    <?php if (!$app->user->is('guest')): ?>
                        <?php if ($app->user->profile->avatar): ?>
                            <img src="<?= $app->user->profile->avatar->transform('avatarSmall')->url ?>" />
                        <?php else: ?>
                            <mc-icon name="user"></mc-icon>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="name">
                    <?= $app->user->profile->name ?>
                </div>
            </div>
            <div class="panel-home__header--user-button">
                <button class="button button--primary button--icon"> <mc-icon name="agent-1"></mc-icon> <?= i::_e('Acessar meu perfil') ?> </button>
            </div>
        </div>
    </header>
    
    <tabs class="panel-home__tabs">
        <tab label="<?php i::esc_attr_e('Principal') ?>" slug="main">
            <div class="panel-home__tabs--main">
                Principal aqui
            </div>
        </tab>
    </tabs>

</div>