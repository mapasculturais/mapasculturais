<?php
use MapasCulturais\i;

$querySent = "";
$queryNotSent = "";

if ($app->user->is('admin')) {
    $querySent = "{'status': 'GT(0)', '@permissions': 'view', 'user': 'EQ(@me)'}";
    $queryNotSent = "{'status': 'EQ(0)', '@permissions': 'view', 'user': 'EQ(@me)'}";  
} else {
    $querySent = "{'status': 'GT(0)', '@permissions': '@control'}";
    $queryNotSent = "{'status': 'EQ(0)', '@permissions': '@control'}";;
}


$this->import('
    mc-icon 
    panel--entity-card
    panel--entity-tabs 
    mc-icon
    registration-card
');
?>

<div class="panel-page registrations">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon opportunity__background">
                    <mc-icon name="opportunity"></mc-icon>
                </div>
                <div class="title__title"> <?= i::_e('Minhas inscrições') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar suas inscrições') ?>
        </p>
        <div class="panel-page__header-actions">
            
        </div>
    </header>

    <tabs class="tabs">
        <tab label="<?= i::_e('Enviadas') ?>" class="tabs_sent" slug="sent">
            
            <div class="registrations__filter">
                <form class="form">
                    <div class="search">
                        <input type="text" class="input" />
                        <button class="button button--icon">
                            <mc-icon name="search"></mc-icon>
                        </button>
                    </div>

                    <select class="order primary__border-solid">
                        <option selected disabled>Ordenar</option>
                    </select>
                </form>
            </div>

            <entities name="registrationsList" type="registration" endpoint="find" :query="<?= $querySent ?>" select="*">
                <template #default="{entities}">

                    <div class="registrations__list">
                        <registration-card v-for="registration in entities" :entity="registration"></registration-card>
                    </div>

                </template>
            </entities>

        </tab>    

        <tab label="<?= i::_e('Não enviadas') ?>" slug="notSent">
            <div class="registrations__filter">
                <form class="form">
                    <div class="search">
                        <input type="text" class="input" />
                        <button class="button button--icon">
                            <mc-icon name="search"></mc-icon>
                        </button>
                    </div>

                    <select class="order primary__border-solid">
                        <option value="name ASC">ordem alfabética</option>
                        <option value="createTimestamp DESC">mais recentes primeiro</option>
                        <option value="createTimestamp ASC">mais antigas primeiro</option>
                        <option value="updateTimestamp DESC">modificadas recentemente</option>
                        <option value="updateTimestamp ASC">modificadas há mais tempo</option>
                    </select>
                </form>
            </div>

            <entities name="registrationsList" type="registration" endpoint="find" :query="<?= $queryNotSent ?>" select="*">
                <template #default="{entities}">

                    <div class="registrations__list">
                        <registration-card v-for="registration in entities" :entity="registration"></registration-card>
                    </div>

                </template>
            </entities>
        </tab> 
    </tabs>
</div>