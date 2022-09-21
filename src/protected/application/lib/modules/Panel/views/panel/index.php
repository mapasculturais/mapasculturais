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
                <div class="entity-cards">
                    
                    <!-- agentes -->
                    <div class="entity-cards__card">
                        <div class="entity-cards__card--header">
                            <div class="entity-cards__card--header-icon agent__background agent__color"> <mc-icon name="agent-1"></mc-icon> </div>
                            <div class="entity-cards__card--header-label"> <?= i::_e('Agentes') ?> </div>
                        </div>
                        <div class="entity-cards__card--counter">
                            <div class="entity-cards__card--counter-num"> <?= $count->agents; ?> </div>
                            <div class="entity-cards__card--counter-label"> <?= i::_e('Agentes') ?> </div>
                        </div>
                        <div class="entity-cards__card--create">
                            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
                        </div>
                    </div>
                    
                    <!-- oportunidades -->
                    <div class="entity-cards__card">
                        <div class="entity-cards__card--header">
                            <div class="entity-cards__card--header-icon opportunity__background opportunity__color"> <mc-icon name="opportunity"></mc-icon> </div>
                            <div class="entity-cards__card--header-label"> <?= i::_e('Oportunidades') ?> </div>
                        </div>

                        <div class="entity-cards__card--counter">
                            <div class="entity-cards__card--counter-num"> <?= $count->opportunities; ?> </div>
                            <div class="entity-cards__card--counter-label"> <?= i::_e('Oportunidades') ?> </div>
                        </div>

                        <div class="entity-cards__card--create">
                            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
                        </div>
                    </div>
                    
                    <!-- eventos -->
                    <div class="entity-cards__card">
                        <div class="entity-cards__card--header">
                            <div class="entity-cards__card--header-icon event__background event__color"> <mc-icon name="event"></mc-icon> </div>
                            <div class="entity-cards__card--header-label"> <?= i::_e('Eventos') ?> </div>
                        </div>

                        <div class="entity-cards__card--counter">
                            <div class="entity-cards__card--counter-num"> <?= $count->events; ?> </div>
                            <div class="entity-cards__card--counter-label"> <?= i::_e('Eventos') ?> </div>
                        </div>

                        <div class="entity-cards__card--create">
                            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
                        </div>
                    </div>

                    <!-- espaços -->
                    <div class="entity-cards__card">
                        <div class="entity-cards__card--header">
                            <div class="entity-cards__card--header-icon space__background space__color"> <mc-icon name="space"></mc-icon> </div>
                            <div class="entity-cards__card--header-label"> <?= i::_e('Espaços') ?> </div>
                        </div>

                        <div class="entity-cards__card--counter">
                            <div class="entity-cards__card--counter-num"> <?= $count->spaces; ?> </div>
                            <div class="entity-cards__card--counter-label"> <?= i::_e('Espaços') ?> </div>
                        </div>

                        <div class="entity-cards__card--create">
                            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
                        </div>
                    </div>

                    <!-- projetos -->
                    <div class="entity-cards__card">
                        <div class="entity-cards__card--header">
                            <div class="entity-cards__card--header-icon project__background project__color"> <mc-icon name="project"></mc-icon> </div>
                            <div class="entity-cards__card--header-label"> <?= i::_e('Projetos') ?> </div>
                        </div>

                        <div class="entity-cards__card--counter">
                            <div class="entity-cards__card--counter-num"> <?= $count->projects; ?> </div>
                            <div class="entity-cards__card--counter-label"> <?= i::_e('Projetos') ?> </div>
                        </div>

                        <div class="entity-cards__card--create">
                            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </tab>
    </tabs>

</div>