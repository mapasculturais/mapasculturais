<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    create-event
    mc-breadcrumb
    mc-tab
    mc-tabs 
    search 
    search-filter-event
    search-list-event 
    search-map-event 
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label' => i::__('Eventos'), 'url' => $app->createUrl('events')],
];
?>
<search page-title="<?php i::esc_attr_e('Eventos') ?>" entity-type="event" :initial-pseudo-query="{'event:term:linguagem':[],'event:term:linguagem':[], 'event:classificacaoEtaria': []}">
    <template v-if="global.auth.isLoggedIn" #create-button>
        <create-event #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Evento') ?></span>
            </button>
        </create-event>
    </template>
    <template #default="{pseudoQuery, changeTab}">
        <mc-tabs  @changed="changeTab($event)" class="search__tabs" sync-hash>
            <template #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label>
            </template>
            <mc-tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="search__tabs--list">
                    <search-list-event :pseudo-query="pseudoQuery"></search-list-event>
                </div>
            </mc-tab>
            <mc-tab icon="map" label="<?php i::esc_attr_e('Mapa') ?>"  slug="map">
                <div class="search__tabs--map">
                    <search-map-event :pseudo-query="pseudoQuery" position="map"></search-map-event>
                </div>
            </mc-tab>
        </mc-tabs>
    </template>
</search>