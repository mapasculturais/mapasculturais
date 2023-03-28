<?php
use MapasCulturais\i;

$this->import('
    create-agent
    search 
    search-filter-agent 
    search-list 
    search-map 
    tabs 
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label' => i::__('Agentes'), 'url' => $app->createUrl('agents')],
];
?>

<search page-title="<?php i::esc_attr_e('Agentes') ?>" entity-type="agent" :initial-pseudo-query="{'term:area':[]}">
    <template #create-button>
        <create-agent #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Agente') ?></span>
            </button>
        </create-agent>
    </template>
    <template #default="{pseudoQuery}">
        <tabs class="search__tabs">
            <template #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label>
            </template>
            <tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="search__tabs--list">
                    <search-list :pseudo-query="pseudoQuery" type="agent" select="name,type,shortDescription,files.avatar,seals,endereco,terms" >
                        <template #filter>
                            <search-filter-agent :pseudo-query="pseudoQuery"></search-filter-agent>
                        </template>
                    </search-list>
                </div>
            </tab>
            <tab icon="map" label="<?php i::esc_attr_e('Mapa') ?>" slug="map">
                <div class="search__tabs--map">
                    <search-map type="agent" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-agent :pseudo-query="pseudoQuery" position="map"></search-filter-agent>
                        </template>
                    </search-map>
                </div>
            </tab>
        </tabs>
    </template>
</search>