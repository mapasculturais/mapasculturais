<?php

use MapasCulturais\i;

$this->import('
    create-project
    search
    search-filter-project
    search-list
    search-map
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label' => i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>

<search entity-type="project" page-title="<?php i::esc_attr_e('Projetos') ?>" :initial-pseudo-query="{type:[]}">
    <template v-if="global.auth.isLoggedIn" #create-button>
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
                <search-list :pseudo-query="pseudoQuery" type="project" select="name,type,shortDescription,files.avatar,seals,terms" >
                    <template #filter>
                        <search-filter-project :pseudo-query="pseudoQuery"></search-filter-project>
                    </template>
                </search-list>
            </div>
        </div>
    </template>
</search>