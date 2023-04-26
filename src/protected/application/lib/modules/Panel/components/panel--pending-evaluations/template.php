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

<div v-if="entities.length > 0" class="panel--pending-evaluations">

    <div class="panel--pending-evaluations__title">
        <label> <?php i::_e('Avaliações disponíveis')?> </label>
    </div>

    <div class="panel--pending-evaluations__content">
        <carousel :settings="settings" :breakpoints="breakpoints" ref="carousel" @slide-end="resizeSlides">
            <slide v-for="entity in entities" :key="entity.id">

                <panel--entity-card :key="entity.id" :entity="entity" class="card">
                    <template #title>
                        {{entity.parent?.name || entity.name}}
                    </template>

                    <template #header-actions>
                    </template>

                    <template #default>
                        <div class="grid-12">
                            <div class="col-12">
                                <?php i::_e('Tipo:') ?> <strong class="opportunity__color">{{entity.type.name}}</strong>
                            </div>
                            <div class="col-12">
                                {{ownerType(entity.ownerEntity)}}: <strong><mc-link :entity="entity.ownerEntity"></mc-link></strong>
                            </div>
                        </div>
                    </template>

                    <template #entity-actions-left>
                        &nbsp;
                    </template>

                    <template #entity-actions-center>
                        &nbsp;
                    </template>

                    <template #entity-actions-right>
                        <mc-link :entity="entity.evaluationMethodConfiguration" route="evaluationsList" class="button button--primary button--icon"> <?= i::__('Avaliar')?> <mc-icon name="arrow-right"></mc-icon> </mc-link>
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