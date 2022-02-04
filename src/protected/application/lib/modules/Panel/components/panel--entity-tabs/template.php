<?php
use MapasCulturais\i;

$this->import('
    tabs,tab,
    panel--entity-actions
    entities
');

$tabs = $tabs ?? [
    'publish' => i::esc_attr__('Publicados'),
    'draft' => i::esc_attr__('Em rascunho'),
    'archived' => i::esc_attr__('Arquivados'),
    'trash' => i::esc_attr__('Lixeira'),
];
?>
<tabs class="entity-tabs">
    <template #header="{ tab }">
        <iconify icon="mdi:archive-outline" v-if="tab.slug === 'archived'"></iconify>
        <iconify icon="mdi:delete-outline" v-else-if="tab.slug === 'trash'"></iconify>
        {{ tab.label }}
    </template>
    <?php foreach($tabs as $status => $label): ?>
    <tab v-if="showTab('<?=$status?>')" cache key="<?$status?>" label="<?=$label?>" slug="<?=$status?>">
        <entities :name="type + ':<?=$status?>'" :type="type" #="{entities}"
            :select="select"
            :query="queries['<?=$status?>']" :limit="50">

            <template v-if="true">
                <div class="entity-tabs__filters panel__row">
                    <input type="search" class="entity-tabs__search-input"
                        aria-label="<?=i::__('Palavras-chave')?>"
                        placeholder="<?=i::__('Buscar por palavras-chave')?>"
                        v-model="entities.query['@keyword']">
                    <button type="button" class="button is-solid" @click="entities.refresh()">
                        <?=i::__('Filtrar')?>
                    </button>
                    <button type="button" class="button is-solid">
                        <iconify icon="mdi:sort" inline></iconify>
                        <?=i::__('Ordenar')?>
                    </button>
                </div>
            </template>

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