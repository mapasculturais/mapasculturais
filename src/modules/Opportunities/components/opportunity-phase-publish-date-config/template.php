<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-link
');
?>
<div class="col-12">
    <div class="grid-12 opportunity-phase-publish-date-config">
        <h4 class="bold col-12">  <?= i::__("Publicação de Resultados") ?></h4>
        <div v-if="phase.publishedRegistrations" class="published">
            <div class="col-4">
                <mc-confirm-button :message="text('despublicar')" @confirm="unpublishRegistration()">
                    <template #button="modal">
                        <button class="button button--primary-outline" @click="modal.open()">
                            <?= i::__("Despublicar") ?>
                        </button>
                    </template>
                </mc-confirm-button>
            </div>
        </div>

        <div v-if="!phase.publishedRegistrations && !firstPhase?.isContinuousFlow" class="grid-12 col-12 notPublished opportunity-phase-publish-date-config__content">
            <div class="opportunity-phase-publish-date-config__left col-4">

                <entity-field v-if="!hideDatepicker" :entity="phase" prop="publishTimestamp" :autosave="3000" :min="minDate" :max="maxDate" classes="col-4 opportunity-phase-publish-date-config__date"></entity-field>
                
                <div v-if="hideDatepicker && phase.publishTimestamp" class="col-4 msgpub-date">
                    <h5 v-if="phase.autoPublish && hideCheckbox">
                        <?= sprintf(
                                i::__("O resultado será publicado automaticamente no dia %s às %s"), 
                                "{{phase.publishTimestamp.date('2-digit year')}}", 
                                "{{phase.publishTimestamp.time('2-digit')}}"
                        ) ?>   
                    </h5>
                    <h5 v-else>
                        <?= sprintf(
                                i::__("O resultado será publicado no dia %s às %s"), 
                                "{{phase.publishTimestamp.date('2-digit year')}}", 
                                "{{phase.publishTimestamp.time('2-digit')}}"
                        ) ?>
                    </h5>
                </div>
                <div v-else-if="hideCheckbox && phase.autoPublish" class="msg-auto-pub col-4">
                    <h5 class="semibold"><?= i::__('O resultado será publicado automaticamente') ?></h5>
                </div>
                <div v-else class="col-4 opportunity-phase-publish-date-config__subtitle">
                    <h5 class="semibold"><?= i::__("A publicação do resultado é opcional.") ?></h5>
                </div>
            </div>
            <div v-if="!hideButton && firstPhase.status > 0" class="col-4">
                <mc-confirm-button :message="text('confirmar_publicacao')" @confirm="publishRegistration()">
                    <template #button="modal">
                        <button class="button button--primary button-config" @click="modal.open()">
                            <?= i::__("Publicar Resultados") ?>
                        </button>
                    </template>
                </mc-confirm-button>
            </div>

            <entity-field v-if="!hideCheckbox" 
                :entity="phase" 
                prop="autoPublish" 
                type="checkbox" 
                :autosave="3000" 
                :disabled="!phase.publishTimestamp" 
                hideRequired 
                classes="col-4 opportunity-phase-publish-date-config__checkbox"></entity-field>
            
        </div>
        
        <div class="col-12 grid-12" v-if="true">
            <div class="col-12" v-if="phase.evaluationMethodConfiguration">
                <entity-field :entity="phase.evaluationMethodConfiguration" prop="publishEvaluationDetails" type="checkbox" :autosave="300" ></entity-field>
            </div>
            <div class="col-12" v-if="phase.evaluationMethodConfiguration">
                <entity-field :entity="phase.evaluationMethodConfiguration" prop="publishValuerNames" type="checkbox" :autosave="300"></entity-field>
            </div>
        </div>
    </div>
</div>