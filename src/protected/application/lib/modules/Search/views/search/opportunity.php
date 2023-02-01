<?php 
use MapasCulturais\i;
 
$this->import('
    search tabs search-list search-map search-filter-opportunity create-opportunity 
    '); /* create-opportunity */
$this->breadcrumb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label'=> i::__('Oportunidades'), 'url' => $app->createUrl('opportunities')],
];
?>

<search page-title="<?php i::esc_attr_e('Oportunidades') ?>" entity-type="opportunity" :initial-pseudo-query="{type:[]}"> 
    <template #create-button>
        <!-- @TODO: Criação e aplicação do componente <create-opportunity> -->
        <create-opportunity></create-opportunity>
    </template>
    <template #default="{pseudoQuery}">
        <div class="tabs-component__panels">
            <div class="search__tabs--list">
                <search-list :pseudo-query="pseudoQuery" type="opportunity">
                    <template #filter>
                        <search-filter-opportunity :pseudo-query="pseudoQuery"></search-filter-opportunity>
                    </template>
                </search-list>
            </div>
        </div>
    </template>
</search>