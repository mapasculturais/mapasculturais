<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
');
?>
<div class="col-12">
    <div class="grid-12 opportunity-phase-publish-date-config">
        <div v-if="phase.publishedRegistrations" class="published">
            <div class="col-4">
                <mc-confirm-button :message="text('despublicar')" @confirm="unpublishRegistration()">
                    <template #button="modal">
                        <button class="button button--text button--text-danger" @click="modal.open()">
                            <?= i::__("Despublicar") ?>
                        </button>
                    </template>
                </mc-confirm-button>
            </div>
            <div v-if="!!phase.publishTimestamp" class="col-4">
                <h5>{{ msgPublishDate }}</h5>
            </div>
        </div>

        <div v-if="!phase.publishedRegistrations" class="grid-12 col-12 notPublished">
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
                <h5><?= i::__('O resultado será publicado automaticamente') ?></h5>
            </div>
            <div v-else class="col-4">
                <h5><?= i::__("A publicação do resultado é opcional.") ?></h5>
            </div>
            
            <entity-field v-if="!hideDatepicker" :entity="phase" prop="publishTimestamp" :autosave="300" :min="minDate" :max="maxDate" classes="col-4"></entity-field>
            
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
                :autosave="300" 
                :disabled="!phase.publishTimestamp" 
                hideRequired 
                classes="col-4"></entity-field>
            
        </div>
    </div>
</div>