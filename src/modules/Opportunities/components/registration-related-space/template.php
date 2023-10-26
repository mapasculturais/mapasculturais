<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-icon
    select-entity
');
?>
<mc-card v-if="useSpaceRelation !== 'dontUse'">
    <template #title>
        <div class="card__title"> 
            <?= i::__("Espaço") ?>
            <div v-if="useSpaceRelation == 'required'" class="obrigatory"> <?= i::__('* Obrigatório') ?> </div>
        </div>
        <div class="card__subtitle">
            <?= i::__("Vincule um espaço a sua inscrição") ?>
        </div>
    </template>
    <template #content>
        <div v-if="relatedSpace" class="registration-related-entity">
            <div class="registration-related-entity__entity">
                <mc-avatar :entity="relatedSpace.space" size="small"></mc-avatar>
                <div class="name">
                    {{relatedSpace.space.name}}
                </div>
            </div>
            <div class="registration-related-entity__actions">
                <select-entity type="space" @select="selectSpace($event)" permissions="">
                    <template #button="{toggle}">
                        <button class="button button--text button--icon button--sm change" @click="toggle()"> 
                            <mc-icon name="exchange"></mc-icon> <?= i::__('Trocar') ?> 
                        </button>
                    </template>
                </select-entity>
                <button class="button button--text button--icon button--sm delete" @click="removeSpace()"> 
                    <mc-icon name="trash"></mc-icon> <?= i::__('Excluir') ?> 
                </button>
            </div>
            
            <div v-if="relatedSpace.status == -5" class="registration-related-entity__status">
                <mc-icon name="exclamation"></mc-icon>
                <?= i::__('A solicitação está pendente') ?>
            </div>
        </div>
        
        <select-entity v-if="!relatedSpace" type="space" @select="selectSpace($event)" permissions="">
            <template #button="{toggle}">
                <button class="button button--primary-outline button--icon button--md" @click="toggle()"> 
                    <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?> 
                </button>
            </template>
        </select-entity>
        <div v-if="registration.__validationErrors.space" class="errors">
            <span>{{registration.__validationErrors.space.join('; ')}}</span>
        </div>
    </template>
</mc-card>