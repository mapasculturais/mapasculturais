<?php 
use MapasCulturais\i;
 
$this->import('
    create-opportunity 
    search 
    search-filter-opportunity
    search-list
    search-map
');

$this->breadcrumb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('site', 'index')],
    ['label'=> i::__('Oportunidades'), 'url' => $app->createUrl('opportunities')],
];
?>
<search page-title="<?php i::esc_attr_e('Oportunidades') ?>" entity-type="opportunity" :initial-pseudo-query="{type:[],'term:area':[]}"> 
    <template v-if="global.auth.isLoggedIn" #create-button>
        <create-opportunity #default="{modal}">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar Oportunidade') ?></span>
            </button>
        </create-opportunity>
    </template>
    <template #default="{pseudoQuery, entity}">
        <div class="tabs-component__panels">
            <div class="search__tabs--list">
                <search-list :pseudo-query="pseudoQuery" type="opportunity" select="name,type,shortDescription,files.avatar,seals,terms,registrationFrom,registrationTo">
                    <template #filter>
                        

                        <search-filter-opportunity :pseudo-query="pseudoQuery"></search-filter-opportunity>
                    </template>
                </search-list>
            </div>
        </div>
    </template>
</search>