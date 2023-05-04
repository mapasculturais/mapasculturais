<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    panel--entity-actions 
');
?>
<article class="panel__row panel-entity-card">
    <header class="panel-entity-card__header">
        <div class="left">
            <div class="panel-entity-card__header--picture">
                <slot name="picture" :entity="entity">
                    <img v-if="entity.files?.avatar" :src="entity.files?.avatar?.transformations?.avatarSmall?.url" alt="">
                    <mc-icon v-if="!entity.files?.avatar" :entity="entity" ></mc-icon>
                </slot>
            </div>
            <div class="panel-entity-card__header--info">
                <h2 class="panel-entity-card__header--info-title">
                    <slot name="title" :entity="entity">
                        {{ entity?.name || entity?.email || entity?.number || entity?.id }}
                    </slot>
                </h2>
                <p class="panel-entity-card__header--info-subtitle">
                    <slot name="subtitle" :entity="entity"></slot>
                </p>
            </div>            
        </div>
        <div class="right">
            <div class="panel-entity-card__header-actions">
                <slot name="header-actions" :entity="entity"></slot>
            </div>
        </div>
    </header>
    <main class="panel-entity-card__main">
        <slot :entity="entity"></slot>
    </main>
    <footer class="panel-entity-card__footer">
        <div class="panel-entity-card__footer-actions">
            <slot name="footer-actions">
                <div class="panel-entity-card__footer-actions left">
                    <slot name="entity-actions-left" :entity="entity">
                        <panel--entity-actions 
                            :entity="entity" 
                            @undeleted="$emit('undeleted', $event)"
                            @deleted="$emit('deleted', $event)"
                            @unpublished="$emit('unpublished', $event)"
                            @archived="$emit('archived', $event)"
                            @published="$emit('published', $event)"
                            :on-delete-remove-from-lists="onDeleteRemoveFromLists"
                            :buttons="leftButtons"
                        ></panel--entity-actions>
                    </slot>
                </div>

                <div class="panel-entity-card__footer-actions right">
                    <slot name="entity-actions-center" >
                    </slot>
                    <slot name="entity-actions-right" >
                        <a :href="entity.singleUrl" class="button button--primary-outline button--icon button-action"><?php i::_e('Acessar') ?> <mc-icon name="arrowPoint-right"></mc-icon></a> 
                        <a v-if="entity.status>=0" :href="entity.editUrl" class="button button--primary button--icon editdraft button-action"><mc-icon name="edit"></mc-icon> <?php i::_e('Editar') ?></a>
                        <panel--entity-actions 
                            v-if="rightButtons"
                            :entity="entity" 
                            @undeleted="$emit('undeleted', $event)"
                            @deleted="$emit('deleted', $event)"
                            @unpublished="$emit('unpublished', $event)"
                            @archived="$emit('archived', $event)"
                            @published="$emit('published', $event)"
                            :on-delete-remove-from-lists="onDeleteRemoveFromLists"
                            :buttons="rightButtons"
                        ></panel--entity-actions>
                    </slot>
                </div>
            </slot>
        </div>
    </footer>

</article>
