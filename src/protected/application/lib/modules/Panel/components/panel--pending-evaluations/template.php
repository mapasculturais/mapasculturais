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
<div v-if="entities.length > 0" class="panel--pending-evaluations">

    <div class="panel--pending-evaluations__title">
        <label> <?php i::_e('Avaliações disponíveis')?> </label>
    </div>

    <div class="panel--pending-evaluations__content">
        <carousel :settings="settings" :breakpoints="breakpoints" ref="carousel" @slide-end="resizeSlides">
            <slide v-for="entity in entities" :key="entity.id">

                <panel--entity-card :key="entity.id" :entity="entity" class="card">
                    
                    <!-- <template #picture>
                        <img v-if="entity?.files.avatar || entity.files?.avatar" :src="entity.parent?.files?.avatar?.transformations?.avatarSmall?.url || entity.files?.avatar?.transformations?.avatarSmall?.url" />
                        <mc-icon v-if="!entity.parent?.files.avatar || !entity.files?.avatar" :entity="entity" ></mc-icon>
                    </template> -->
                
                
                    <template #title>
                        <h2 class="bold">{{entity.parent?.name || entity.name}}</h2>
                    </template>

                    <template #header-actions>
                    </template>

                    <template #default>
                        <div class="type-evaluation grid-12">
                            <div class="type-evaluation__type col-12">
                                <label class="entity-label"><?php i::_e('Tipo:') ?></label> <strong class="opportunity__color entity-strong">{{entity.type.name}}</strong>
                            </div>
                            <div class="type-evaluation__content col-12">
                                <label class="type-evaluation__content--label">{{ownerType(entity.ownerEntity)}}:</label> <strong class="type-evaluation__content--strong"><mc-link :entity="entity.ownerEntity"></mc-link></strong>
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
                        <mc-link :entity="entity" route="opportunityEvaluations" class="button-evaluate button button--primary button--icon"> <?= i::__('Avaliar')?> <mc-icon name="arrow-right-ios"></mc-icon> </mc-link>
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