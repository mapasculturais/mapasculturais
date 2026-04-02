<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-multiselect
');
?>

<div class="oc-social-media">
    <div class="options">
        <div>
            <mc-tag-list :tags="socialmedia" :labels="socialmediaLabels" classes="opportunity__background" @remove="change($event)" editable></mc-tag-list>
        </div>
        <div>
            <mc-multiselect :model="socialmedia" :items="socialmediaList" @selected="change($event)" @removed="change($event)">
                <template #default="{toggleMultiselect}">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggleMultiselect()">
                        <?php i::_e("Adicionar") ?>
                        <mc-icon name="add"></mc-icon>
                    </button>
                </template>
            </mc-multiselect>
        </div>
    </div>
    <div v-if="entity.socialmedia?.length > 0" class="fields">
        <template v-for="media in entity.socialmedia">
            <div class="field">
                <label class="field__title">{{socialmediaLabels[media]}}</label>
                <input v-model="socialmediaData[media]" :name="media" type="text" autocomplete="off" :placeholder="`${media}.com.br/perfil`">
            </div>
        </template>
    </div>

    <div class="no-data" v-if="entity.socialmedia?.length <= 0">
        <?php i::_e("Nenhuma rede social foi configurada") ?>
    </div>
</div>