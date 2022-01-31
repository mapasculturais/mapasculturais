<?php 
use MapasCulturais\i;

$this->import('
    tabs,tab, 
    entities
');

$tabs = $tabs ?? [
    'publish' => i::esc_attr__('Publicados'),
    'draft' => i::esc_attr__('Em rascunho'),
    'archived' => i::esc_attr__('Arquivados'),
    'trash' => i::esc_attr__('Lixeira'),
];
?>
<tabs> 
    <?php foreach($tabs as $status => $label): ?>
    <tab v-if="showTab('<?=$status?>')" name="<?=$label?>" nav-class="<?=$status?>" :cache-tls="cacheTls">
        <entities :name="type + ':<?=$status?>'" :type="type" #="{entities}"
            :select="select"
            :query="queries['<?=$status?>']" :limit="50">
            <article v-for="entity in entities" class="objeto">
                <h1><a :href="entity.singleUrl">{{entity.name}}</a></h1>
                <slot :entity="entity">{{entity.id}}</slot>
                <div class="entity-actions">
                    <panel--entity-actions :entity="entity"></panel--entity-actions>
                </div>
            </article>
        </entities>
    </tab>
    <?php endforeach ?>
</tabs>