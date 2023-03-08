<?php

use MapasCulturais\i;

$this->import('
    create-project
    search
    search-filter-project
    search-list
    search-map
    tabs
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label' => i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>

<search page-title="<?php i::esc_attr_e('Projetos') ?>" entity-type="project" :initial-pseudo-query="{type:[]}">
    <template #create-button>
        <create-project #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Projeto') ?></span>
            </button>
        </create-project>
    </template>
    <template #default="{pseudoQuery}">
        <div class="tabs-component__panels">
            <div class="search__tabs--list">
                <search-list :pseudo-query="pseudoQuery" type="project">
                    <template #filter>
                        <search-filter-project :pseudo-query="pseudoQuery"></search-filter-project>
                    </template>
                </search-list>
            </div>
        </div>
    </template>
</search>