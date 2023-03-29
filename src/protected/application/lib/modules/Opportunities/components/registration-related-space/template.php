<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 * 
 * @todo renomear componente
 */

use MapasCulturais\i;
?>

<mapas-card v-if="useSpaceRelation !== 'dontUse'">
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
        <div v-if="relatedSpace" class="registration-select-entity">
            <div class="registration-select-entity__entity">
                <div class="image">
                    <img v-if="relatedSpace.files.avatar" :src="relatedSpace.files?.avatar?.transformations?.avatarMedium.url" />
                    <mc-icon v-if="!relatedSpace.files.avatar" name="image"></mc-icon>
                </div>
                <div class="name">
                    {{relatedSpace.name}}
                </div>
            </div>
            <div class="registration-select-entity__actions">
                <select-entity type="space" @select="selectSpace($event)">
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
        </div>
        
        <select-entity v-if="!relatedSpace" type="space" @select="selectSpace($event)">
            <template #button="{toggle}">
                <button class="button button--primary-outline button--icon button--md" @click="toggle()"> 
                    <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?> 
                </button>
            </template>
        </select-entity>
    </template>
</mapas-card>