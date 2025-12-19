<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-card
    mc-confirm-button
    mc-icon
    mc-popover
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-seals', 'before'); ?>

<div :class="classes" v-if="entity.seals.length > 0 || editable" class="entity-seals">
    <?php $this->applyTemplateHook('entity-seals', 'begin'); ?>
    <h4 class="entity-seals__title bold">
        <slot name="title">{{title}}</slot>
    </h4>
    
    <div class="entity-seals__seals">

        <div class="entity-seals__seals--seal" v-for="seal in entity.seals">
            <div class="seal-icon" v-tooltip="seal.name">
                <mc-popover openside="down-right" classes="relation-popover">
                    <template #button="{toggle}">
                        <div v-if="seal.files?.avatar" class="image" @click="toggle">
                            <mc-avatar :entity="seal" size="small" square></mc-avatar>
                        </div>
                        <div v-if="!(seal.files?.avatar)" @click="toggle">
                            <mc-icon name="seal"></mc-icon>
                        </div>
                    </template>

                    <template #default="popover">
                        <mc-card class="relation-card">
                            <div class="relation-card__close" @click="popover.close()">
                                <mc-icon name="close"></mc-icon>
                            </div>

                            <div class="relation-card__header">
                                <mc-avatar :entity="seal" size="small"></mc-avatar>
                                <p class="name">
                                    {{seal.name}}
                                </p>
                            </div>

                            <div class="relation-card__content">
                                <div class="tags">
                                    <div class="tags__label">
                                       <?= i::__("Data de criação") ?> 
                                    </div>
                                    <div class="tags__tagsList">
                                        {{formatDate(seal.createTimestamp)}}
                                    </div>
                                    <div class="tags__label">
                                       <?= i::__("Descrição curta") ?> 
                                    </div>
                                    <div class="tags__tagsList">
                                        {{seal.shortDescription}}
                                    </div>
                                </div>

                                <a v-if="seal?.enableCertificatePage" :href="seal.singleUrl" class="link ">
                                    <?= i::__("Acessar") ?>
                                </a>
                            </div>
                        </mc-card>
                    </template>
                </mc-popover>

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