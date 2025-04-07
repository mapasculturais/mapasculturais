<?php

use MapasCulturais\i;

$this->import('
    create-project
    search
    search-filter-project
    search-list
    search-map
    mc-tabs
    mc-tab
    project-table
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label' => i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>

<search entity-type="project" page-title="<?= htmlspecialchars($this->text('title', i::__('Projetos'))) ?>" :initial-pseudo-query="{type:[]}">
    <template v-if="global.auth.isLoggedIn" #create-button>
        <create-project #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Projeto') ?></span>
            </button>
        </create-project>
    </template>
    <template #default="{pseudoQuery}">
        <mc-tabs class="search__tabs" sync-hash>
            <template #before-tablist>
                <label class="search__tabs--before">
                    <?= i::_e('Visualizar como:') ?>
                </label>
            </template>
            <mc-tab icon="list" label="<?php i::esc_attr_e('Lista') ?>" slug="list">
                <div class="tabs-component__panels">
                    <div class="search__tabs--list">
                        <search-list :pseudo-query="pseudoQuery" type="project" select="name,type,shortDescription,files.avatar,seals,terms" >
                            <template #filter>
                                <search-filter-project :pseudo-query="pseudoQuery"></search-filter-project>
                            </template>
                        </search-list>
                    </div>
                </div>
            </mc-tab>
            <mc-tab v-if="global.auth.is('admin')" icon="table-view" label="<?php i::esc_attr_e('Tabela') ?>" slug="tables">
                <project-table></project-table>
            </mc-tab>
        </mc-tabs>
    </template>
</search>