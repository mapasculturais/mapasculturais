<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon
    mc-title
    panel--entity-actions
    opportunity-create-based-model 

');
?>
<article class="panel__row panel-entity-models-card" v-if="showModel">
    <header class="panel-entity-models-card__header">
        <div class="left">
            <slot name="picture" :entity="entity">
                <mc-avatar :entity="entity" size="medium"></mc-avatar>
            </slot>
            <div class="panel-entity-models-card__header--info">
                <slot name="title" :entity="entity">

                    <a v-if="entity.currentUserPermissions?.modify" :href="entity.singleUrl" class="panel-entity-models-card__header--info-link">
                        <mc-title tag="h2" :shortLength="100" :longLength="110">
                            {{ entity.name }}
                        </mc-title>
                    </a>
                    <mc-title v-if="!entity.currentUserPermissions?.modify" tag="h2" :shortLength="100" :longLength="110">
                        {{ entity.name }}
                    </mc-title>          
                </slot>
            </div>
        </div>
        <div class="right">
            <div class="panel-entity-models-card__header-actions">
                <slot name="header-actions" :entity="entity">
                    <li v-if="getTypeModel == typeModels.MODEL_OFFICIAL" class="tag-official mc-tag-list__tag">    
                        {{ typeModels.MODEL_OFFICIAL }}
                    </li>
                    <li v-if="getTypeModel == typeModels.MODEL_PUBLIC" class="tag-public mc-tag-list__tag">    
                        {{ typeModels.MODEL_PUBLIC }}
                    </li>
                    <li v-if="getTypeModel == typeModels.MODEL_PRIVATE" class="tag-private mc-tag-list__tag">    
                        {{ typeModels.MODEL_PRIVATE }}
                    </li>
                </slot>
            </div>
        </div>
    </header>
    <main class="panel-entity-models-card__main">
        <span class="card-info"></span>
        <div class="card-desc">
            <div v-for="model in models" :key="model.id">
                <span v-if="model.id == entity.id">
                    <p>{{ model.descricao.substring(0, 150) }}</p>
                    <mc-icon name="project" class="icon-model"></mc-icon> 
                    <strong><?=i::__('Tipo de Oportunidade: ')?></strong>{{ entity.type.name }}
                    <br>
                    <mc-icon name="circle-checked" class="icon-model"></mc-icon>
                    <strong><?=i::__('Número de fases: ')?></strong>{{ model.numeroFases }}
                    <br>
                    <mc-icon name="date" class="icon-model"></mc-icon>
                    <strong><?=i::__('Tempo estimado: ')?></strong>{{ model.tempoEstimado }}
                    <br>
                    <mc-icon name="agent" class="icon-model"></mc-icon>
                    <strong><?=i::__('Tipo de agente: ')?></strong> {{ model.tipoAgente }}
                    <br><br>
                    <?php if($app->user->is('admin')): ?>
                        <div v-if="entity.currentUserPermissions?.modify">
                            <label class="switch" >
                                <input type="checkbox" v-model="isModelPublic" />
                                <span class="slider round"></span>
                            </label>
                            <span class="switch-text"><?= i::__("Modelo público") ?></span>
                        </div>
                        <br><br>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </main>
    <footer class="panel-entity-models-card__footer">
        <div class="panel-entity-models-card__footer-actions">
            <slot name="footer-actions">
                <div class="panel-entity-models-card__footer-actions left">
                    <slot name="entity-actions-left" :entity="entity">
                        <panel--entity-actions 
                            :entity="entity" 
                            @deleted="$emit('deleted', $event)"
                            :on-delete-remove-from-lists="onDeleteRemoveFromLists"
                            :buttons="leftButtons"
                        ></panel--entity-actions>
                    </slot>
                </div>
                <div class="panel-entity-models-card__footer-actions right">
                    <slot name="entity-actions-center" >
                    </slot>
                    <slot name="entity-actions-right" >
                        <div v-if="showModel && entity.status != -2 && entity.__objectType == 'opportunity' && entity.isModel == 1">
                            <opportunity-create-based-model :entitydefault="entity" classes="col-12"></opportunity-create-based-model>
                        </div>
                    </slot>
                </div>
            </slot>
        </div>
    </footer>

</article>
