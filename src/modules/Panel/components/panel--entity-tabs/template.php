<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-entities
    mc-tab
    mc-tabs
    panel--entity-card
    registration-card
');

$tabs = $tabs ?? [
    'publish' => i::esc_attr__('Publicados'),
    'draft' => i::esc_attr__('Em rascunho'),
    'granted' => i::esc_attr__('Com permissão'),
    'archived' => i::esc_attr__('Arquivados'),
    'trash' => i::esc_attr__('Lixeira'),
];

$this->applyComponentHook('.tabs', [&$tabs]);

$sort_options = [
    'name ASC' => i::__('Ordem alfabética'),
    'createTimestamp DESC' => i::__('Mais recentes primeiro'),
    'createTimestamp ASC' => i::__('Mais antigas primeiro'),
    'updateTimestamp DESC' => i::__('Modificadas recentemente'),
    'updateTimestamp ASC' => i::__('Modificadas há mais tempo'),
];

$this->applyComponentHook('.sortOptions', [&$tabs]);

?>
<mc-tabs class="entity-tabs" sync-hash>
    <?php $this->applyComponentHook('begin') ?>
    <template #header="{ tab }">
        <?php $this->applyComponentHook('tab', 'begin') ?>
        <mc-icon v-if="tab.slug === 'archived'" name="archive"></mc-icon>
        <mc-icon v-else-if="tab.slug === 'trash'" name="trash"></mc-icon>
        {{ tab.label }}
        <?php $this->applyComponentHook('tab', 'end') ?>
    </template>
    <?php foreach($tabs as $status => $label): ?>
    <?php $this->applyComponentHook($status, 'before') ?>
    <mc-tab v-if="showTab('<?=$status?>')" cache key="<?$status?>" label="<?=$label?>" slug="<?=$status?>">
        <?php $this->applyComponentHook($status, 'begin') ?>
        <mc-entities :name="type + ':<?=$status?>'" :type="type" 
            :select="select"
            :query="queries['<?=$status?>']" 
            :limit="50" 
            :order="queries['<?=$status?>']['@order']"
            watch-query>
            <template #header="{entities}">
                <form class="entity-tabs__filters panel__row" @submit="$event.preventDefault();">
                    <slot name="filters">
                        <input type="search" class="entity-tabs__search-input"
                            aria-label="<?=i::__('Palavras-chave')?>"
                            placeholder="<?=i::__('Buscar por palavras-chave')?>"
                            v-model="queries['<?=$status?>']['@keyword']">
                        
                        <slot name="filters-additional" :entities="entities" :query="queries['<?=$status?>']"></slot>
                        <label> <?= i::__ ("Ordernar por:") ?>
                            <select class="entity-tabs__search-select primary__border--solid" v-model="queries['<?=$status?>']['@order']">
                                <?php foreach($sort_options as $value => $label): ?>
                                    <option value="<?= htmlentities($value) ?>"><?= htmlentities($label) ?></option>    
                                <?php endforeach ?>
                            </select>
                        </label>

                    </slot>
                </form>
            </template>

            <template #default="{entities}">
                <slot name='before-list' :entities="entities" :query="queries['<?=$status?>']"></slot>
                <slot v-for="entity in entities" :key="entity.__objectId" :entity="entity" :moveEntity="moveEntity">
                    <registration-card v-if="entity.__objectType=='registration'" :entity="entity" pictureCard hasBorders class="panel__row"></registration-card>
                    <panel--entity-card  v-if="entity.__objectType!='registration'" :key="entity.id" :entity="entity" 
                        @undeleted="moveEntity(entity, $event)" 
                        @deleted="moveEntity(entity, $event)" 
                        @archived="moveEntity(entity, $event)" 
                        @published="moveEntity(entity, $event)"
                        :on-delete-remove-from-lists="false"
                        >
                        <template #title="{ entity }">
                            <slot name="card-title" :entity="entity"></slot>
                        </template>
                        <template #subtitle="{ entity }">
                            <slot name="card-content"  :entity="entity">
                                <span v-if="entity.type">
                                    <?=i::__('Tipo: ')?> <strong>{{ entity.type.name }}</strong>
                                </span>
                            </slot>
                        </template>
                        <template #entity-actions-left>
                            <slot name="entity-actions-left" :entity="entity"></slot>
                        </template>
                        <template #entity-actions-center>
                            <slot name="entity-actions-center" :entity="entity"></slot>
                        </template>
                        <template #entity-actions-right>
                            <slot name="entity-actions-right" :entity="entity"></slot>
                        </template>
                    </panel--entity-card>
                </slot>
                <slot name='after-list' :entities="entities" :query="queries['<?=$status?>']"></slot>
           </template>
        </mc-entities>
        <?php $this->applyComponentHook($status, 'end') ?>
    </mc-tab>
    <?php $this->applyComponentHook($status, 'after') ?>
    <?php endforeach ?>
    <?php $this->applyComponentHook('end') ?>
</mc-tabs>