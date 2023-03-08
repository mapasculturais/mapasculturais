<?php 
use MapasCulturais\i;

$this->import('
    create-space
    search
    search-filter-space
    search-list
    search-map
    tabs
');

$this->breadcrumb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('spaces')],
];
?>

<search page-title="<?php i::esc_attr_e('Espaços') ?>" entity-type="space" :initial-pseudo-query="{'term:area':[], type:[]}">    
    <template #create-button>
        <create-space #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Espaço') ?></span>
            </button>
        </create-space>
    </template>
    <template #default="{pseudoQuery, changeTab}">        
        <tabs @changed="changeTab($event)" class="search__tabs">
            <template  #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label> 
            </template>
            <tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="search__tabs--list">
                    <search-list :pseudo-query="pseudoQuery" type="space">
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery"></search-filter-space>
                        </template>
                    </search-list>
                </div>
            </tab>
            <tab icon="map" label="<?php i::esc_attr_e('Mapa') ?>" slug="map">
                <div class="search__tabs--map">
                    <search-map type="space" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery" position="map"></search-filter-space>
                        </template>
                    </search-map>
                </div>
            </tab>
        </tabs>
    </template>
</search>