<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    entities
    panel--entity-card
    tab
    tabs
');

$tabs = $tabs ?? [
    'publish' => i::esc_attr__('Publicados'),
    'draft' => i::esc_attr__('Em rascunho'),
    'granted' => i::esc_attr__('Concedidos'),
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
                            <select class="entity-tabs__search-select primary__border-solid" v-model="queries['<?=$status?>']['@order']">
                                <option value="name ASC"><?= i::__('ordem alfabética') ?></option>
                                <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                                <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                                <option value="updateTimestamp DESC" selected><?= i::__('modificadas recentemente') ?></option>
                                <option value="updateTimestamp ASC"><?= i::__('modificadas há mais tempo') ?></option>
                            </select>
                        </label>

                    </slot>
                </form>
            </template>

            <template #default="{entities}">
                <slot name='before-list' :entities="entities" :query="queries['<?=$status?>']"></slot>
                <slot v-for="entity in entities" :key="entity.__objectId" :entity="entity" :moveEntity="moveEntity">
                    <panel--entity-card :key="entity.id" :entity="entity" 
                        @undeleted="moveEntity(entity)" 
                        @deleted="moveEntity(entity)" 
                        @archived="moveEntity(entity)" 
                        @published="moveEntity(entity)"
                        :on-delete-remove-from-lists="false"
                        >
                        <template #title="{ entity }">
                            <slot name="card-title" :entity="entity"></slot>
                        </template>
                        <template #header-actions="{ entity }">
                            <slot name="card-actions">
                                <!-- <button class="entity-card__header-action">
                                    <mc-icon name="favorite"></mc-icon>
                                    <span>< ?=i::__('Favoritar')?></span>
                                </button> -->
                                <?php $this->applyTemplateHook('entity-tabs-card-action', 'begin'); ?>
                                <?php $this->applyTemplateHook('entity-tabs-card-action', 'end'); ?>
                            </slot>
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
        </entities>
    </tab>
    <?php endforeach ?>
    <?php $this->applyTemplateHook('entity-tabs', 'end') ?>
</tabs>
<?php $this->applyTemplateHook('entity-tabs', 'after') ?>