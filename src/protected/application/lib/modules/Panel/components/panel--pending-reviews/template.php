<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    entity-card
    loading
    mc-link
');
?>
<loading :condition="loading"></loading>

<div v-if="entities.length > 0" class="panel--pending-reviews">

    <div class="panel--pending-reviews__title">
        <label> <?php i::_e('Avaliações disponíveis')?> </label>
    </div>

    <div class="panel--pending-reviews__content">
        <carousel :settings="settings" :breakpoints="breakpoints" ref="carousel" @slide-end="resizeSlides">
            <slide v-for="entity in entities" :key="entity.id">

                <panel--entity-card :key="entity.id" :entity="entity" class="card">
                    <template #title>
                        {{entity.parent?.name || entity.name}}
                    </template>

                    <template #header-actions="{entity}">
                    </template>

                    <template #default="{entity}">
                        <div class="grid-12">
                            <div class="col-12">
                                <?php i::_e('Tipo:') ?> <strong class="opportunity__color">{{entity.type}}</strong>
                            </div>
                            <div class="col-12">
                                &lt; Entidade &gt;: <strong></strong>
                            </div>
                        </div>
                    </template>

                    <template #entity-actions-left="{entity}">
                        &nbsp;
                    </template>

                    <template #entity-actions-center="{entity}">
                        &nbsp;
                    </template>

                    <template #entity-actions-right="{entity}">
                        <mc-link :entity="entity.evaluationMethodConfiguration" route="evaluationsList" ><?= i::__('Avaliar')?></mc-link>
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