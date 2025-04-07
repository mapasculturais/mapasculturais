<?php 
use MapasCulturais\i;
 
$this->import('
    create-opportunity 
    search 
    search-filter-opportunity
    search-list
    search-map
    mc-tabs
    mc-tab
    opportunity-table
');

$this->breadcrumb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label'=> i::__('Oportunidades'), 'url' => $app->createUrl('opportunities')],
];
?>
<search page-title="<?= htmlspecialchars($this->text('title', i::__('Oportunidades'))) ?>" entity-type="opportunity" :initial-pseudo-query="{type:[],'term:area':[]}"> 
    <template v-if="global.auth.isLoggedIn" #create-button>
        <create-opportunity #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Oportunidade') ?></span>
            </button>
        </create-opportunity>
    </template>

    <template #default="{pseudoQuery, entity}">
        <mc-tabs class="search__tabs" sync-hash>
            <template #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label>
            </template>
            <mc-tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="tabs-component__panels">
                    <div class="search__tabs--list">
                        <search-list :pseudo-query="pseudoQuery" type="opportunity" select="name,type,shortDescription,files.avatar,seals,terms,registrationFrom,registrationTo,hasEndDate,isContinuousFlow">
                            <template #filter>
                                

                                <search-filter-opportunity :pseudo-query="pseudoQuery"></search-filter-opportunity>
                            </template>
                        </search-list>
                    </div>
                </div>
            </mc-tab>
            <mc-tab v-if="global.auth.is('admin')" icon="table-view" label="<?php i::esc_attr_e('Tabela') ?>" slug="tables">
                <opportunity-table></opportunity-table>
            </mc-tab>
        </mc-tabs>
    </template>
</search>