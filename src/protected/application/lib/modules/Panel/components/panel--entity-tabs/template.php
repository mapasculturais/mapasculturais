<?php
use MapasCulturais\i;

$this->import('
    tabs,tab,
    panel--entity-card
    entities
');

$tabs = $tabs ?? [
    'publish' => i::esc_attr__('Publicados'),
    'draft' => i::esc_attr__('Em rascunho'),
    'archived' => i::esc_attr__('Arquivados'),
    'trash' => i::esc_attr__('Lixeira'),
];
?>
<?php $this->applyTemplateHook('entity-tabs', 'before') ?>
<tabs class="entity-tabs">
    <?php $this->applyTemplateHook('entity-tabs', 'begin') ?>
    <template #header="{ tab }">
        <mc-icon v-if="tab.slug === 'archived'" name="archive"></mc-icon>
        <mc-icon v-else-if="tab.slug === 'trash'" name="trash"></mc-icon>
        {{ tab.label }}
    </template>
    <?php foreach($tabs as $status => $label): ?>
    <tab v-if="showTab('<?=$status?>')" cache key="<?$status?>" label="<?=$label?>" slug="<?=$status?>">
        <entities :name="type + ':<?=$status?>'" :type="type" 
            :select="select"
            :query="queries['<?=$status?>']" 
            :limit="50" watch-query>

            <template v-if="true" #header="{entities}">
                <form class="entity-tabs__filters panel__row" @submit="$event.preventDefault();">
                    <input type="search" class="entity-tabs__search-input"
                        aria-label="<?=i::__('Palavras-chave')?>"
                        placeholder="<?=i::__('Buscar por palavras-chave')?>"
                        v-model="queries['<?=$status?>']['@keyword']">

                    <select v-model="queries['<?=$status?>']['@order']">
                        <option value="name ASC"><?= i::__('ordem alfabética') ?></option>
                        <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                        <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                        <option value="updateTimestamp DESC"><?= i::__('modificadas recentemente') ?></option>
                        <option value="updateTimestamp ASC"><?= i::__('modificadas há mais tempo') ?></option>
                    </select>
                </form>
            </template>

            <template #default="{entities}">
                <slot v-for="entity in entities" :entity="entity">
                    <panel--entity-card :key="entity.id" :entity="entity" 
                        @deleted="moveEntity(entity)" 
                        @archived="moveEntity(entity)" 
                        @published="moveEntity(entity)"
                        >
                        <template #title="{ entity }">
                            <slot name="card-title" :entity="entity"></slot>
                        </template>
                        <template #header-actions="{ entity }">
                            <slot name="card-actions">
                                <button class="entity-card__header-action">
                                    <mc-icon name="favorite"></mc-icon>
                                    <span><?=i::__('Favoritar')?></span>
                                </button>
                            </slot>
                        </template>
                        <template #subtitle="{ entity }">
                            <slot name="card-content" :entity="entity">
                                <span v-if="entity.type">
                                    <?=i::__('Tipo: ')?> <strong>{{ entity.type.name }}</strong>
                                </span>
                            </slot>
                        </template>
                    </panel--entity-card>
                </slot>
           </template>
        </entities>
    </tab>
    <?php endforeach ?>
    <?php $this->applyTemplateHook('entity-tabs', 'end') ?>
</tabs>
<?php $this->applyTemplateHook('entity-tabs', 'after') ?>