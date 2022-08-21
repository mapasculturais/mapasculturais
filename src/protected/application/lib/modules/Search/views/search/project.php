<?php 
use MapasCulturais\i;
 
$this->import('
    search tabs search-list search-map search-filter-project 
    '); /* create-project */
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>

<search page-title="Projetos" entity-type="project" >    

    <template #create-button>
        Botão criar projeto<!-- <create-project></create-project> -->
    </template>

    <template #default="{pseudoQuery}">
        <tabs class="search__tabs">
            <template  #before-tablist>
                <label class="search__tabs--before">
                    Visualizar como:
                </label> 
            </template>
            
            <tab icon="list" label="Lista" slug="list">
                <div class="search__tabs--list">

                    <search-list :pseudo-query="pseudoQuery" type="project">
                        <template #filter>
                            <search-filter-project :pseudo-query="pseudoQuery"></search-filter-project>
                        </template>
                    </search-list>

                </div>
            </tab>
        </tabs>
    </template>
</search>