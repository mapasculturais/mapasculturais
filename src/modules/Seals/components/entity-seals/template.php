<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
    mc-icon
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-seals', 'before'); ?>

<div :class="classes" v-if="entity.seals.length > 0 || editable" class="entity-seals">
    <?php $this->applyTemplateHook('entity-seals', 'begin'); ?>
    <h4 class="entity-seals__title bold"> {{title}} </h4>
    <div class="entity-seals__seals">

        <div class="entity-seals__seals--seal" v-for="seal in entity.seals">
            <div class="seal-icon">
                <a :href="seal.singleUrl" class="link ">
                    <div v-if="seal.files?.avatar" class="image">
                        <mc-avatar :entity="seal" size="small" square></mc-avatar>
                    </div>
                    <div v-if="!(seal.files?.avatar)">
                        <mc-icon name="seal"></mc-icon>
                    </div>
                </a>
                <div v-if="editable" class="icon">
                    <mc-confirm-button @confirm="removeSeal(seal)">
                        <template #button="modal">
                            <mc-icon @click="modal.open()" name="delete"></mc-icon>
                        </template>
                        <template #message="message">
                            <?php i::_e('Remover selo?') ?>
                        </template>
                    </mc-confirm-button>
                </div>
            </div>
            <span class="seal-label" v-if="showName">{{seal.name}}</span>
        </div>
        <select-entity v-if="editable" type="seal" @select="addSeal($event)" :query="query" openside="down-right">
            <template #button="{ toggle }">
                <div class="entity-seals__seals--addSeal" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                </div>
            </template>
        </select-entity>
    </div>
    <?php $this->applyTemplateHook('entity-seals', 'end'); ?>
</div>
<?php $this->applyTemplateHook('entity-seals', 'after'); ?>