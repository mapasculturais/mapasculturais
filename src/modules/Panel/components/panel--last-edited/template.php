<?php
/**
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-card
    mc-loading
');
?>
<mc-loading :condition="loading"></mc-loading>
<div v-if="entities.length > 0" class="panel--last-edited">
    <div class="panel--last-edited__content">
        <div class="panel--last-edited__content-title">
            <label> <?php i::_e('Editados recentemente')?> </label>
        </div>
        <div class="panel--last-edited__content-cards">
            <carousel :settings="settings" :breakpoints="breakpoints" ref="carousel" @slide-end="resizeSlides">
                <slide v-for="entity in entities" :key="entity.id">
                    <panel--entity-card :key="entity.id" :entity="entity" class="card">
                        <template #title="{entity}">
                            <mc-title size="small" tag="h4" :shortLength="0" :longLength="1000" class="bold">{{entity.name}}</mc-title>
                        </template>
                        <template #header-actions="{entity}">
                            <div :class="[entity.__objectType+'__background', 'card-actions--tag']">
                                <mc-icon :name="entity.__objectType"></mc-icon>    
                                <span v-if="entity.__objectType=='agent'"> <?= i::_e('Agente') ?> </span>
                                <span v-if="entity.__objectType=='space'"> <?= i::_e('Espaço') ?> </span>
                                <span v-if="entity.__objectType=='event'"> <?= i::_e('Evento') ?> </span>
                                <span v-if="entity.__objectType=='project'"> <?= i::_e('Projeto') ?> </span>
                                <span v-if="entity.__objectType=='opportunity'"> <?= i::_e('Oportunidade') ?> </span>
                            </div>
                        </template>
                        <template #default="{entity}">
                            <div class="grid-12">
                                <div class="col-12">
                                    <?php i::_e('Modificado em') ?> <strong>{{entity.updateTimestamp.date('2-digit year')}} {{entity.updateTimestamp.time('full')}}</strong>
                                </div>
                            </div>
                            <span v-if="entity.shortDescription">
                               {{showShort(entity.shortDescription)}}
                            </span>
                        </template>
                        <template #entity-actions-left="{entity}">
                            &nbsp;
                        </template>
                        <template #entity-actions-center="{entity}">
                            &nbsp;
                        </template>
                    </panel--entity-card>
                </slide>    
                <template #addons>
                    <div class="actions">
                        <navigation />
                    </div>
                </template>
            </carousel>
        </div>
    </div>
</div>