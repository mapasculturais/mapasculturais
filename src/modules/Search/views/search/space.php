<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    create-space
    mc-tab
    mc-tabs
    search
    search-filter-space
    search-list
    search-map
    space-table
');

$this->breadcrumb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('spaces')],
];
?>
<search page-title="<?= htmlspecialchars($this->text('title', i::__('Espaços'))) ?>" entity-type="space" :initial-pseudo-query="{'term:area':[], type:[]}">    
    <template #create-button>
        <create-space v-if="global.auth.isLoggedIn" #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Espaço') ?></span>
            </button>
        </create-space>
    </template>
    <template #default="{pseudoQuery, changeTab}">        
        <mc-tabs @changed="changeTab($event)" class="search__tabs" sync-hash>
            <template  #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label> 
            </template>
            <mc-tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="search__tabs--list">
                    <search-list :pseudo-query="pseudoQuery" type="space" select="name,type,shortDescription,files.avatar,seals,endereco,terms,acessibilidade" >
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery"></search-filter-space>
                        </template>
                    </search-list>
                </div>
            </mc-tab>
            <mc-tab icon="map" label="<?php i::esc_attr_e('Mapa') ?>" slug="map">
                <div class="search__tabs--map">
                    <search-map type="space" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery" position="map"></search-filter-space>
                        </template>
                    </search-map>
                </div>
            </mc-tab>
            <mc-tab v-if="global.auth.is('admin')" icon="table-view" label="<?php i::esc_attr_e('Tabela') ?>" slug="tables">
                <space-table></space-table>
            </mc-tab>
        </mc-tabs>
    </template>
</search>