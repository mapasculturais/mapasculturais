<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
    mc-modal
');
?>

<mc-modal title="<?= i::esc_attr__('Aplicar resultados das avaliações') ?>" classes="apply-evaluations">

    <template #button="modal">
        <button class="button button--primary button--icon col-4" @click="modal.open()">
            <mc-icon name="add"></mc-icon>
            <?php i::_e('Aplicar resultados das avaliações') ?>
        </button>
    </template>

    <template #default>
        <div class="grid-12">
            <div class="col-12 apply-evaluations__range">
                <Slider :step="-1" :tooltips="true" :max="maxResult" v-model="applyData.from"></Slider>
            </div>
            <div class="col-12">
                <input class="col-6" v-model="applyData.from[0]" type="number" step="0.01"/>
                <input class="col-6" v-model="applyData.from[1]" type="number" step="0.01" />
            </div>
            <div class="field col-12">
                <label><?php i::_e('Status') ?></label>
                <select v-model="applyData.to">
                    <option v-for="item in statusList" :value="item.value">{{item.label}}</option>
                </select>
            </div>
            <h5 class="semibold col-12"> <?= i::__("Se você preferir não marcar a caixa abaixo, as avaliações serão aplicadas somente nas inscrições que com o status 'Pendente'.") ?> </h5>
            <div class="field col-12">
                <label class="input__label input__checkboxLabel">
                    <input type="checkbox" v-model="applyData.applyAll">
                    <?php i::_e('Aplicar para todas as inscrições enviadas') ?>
                </label>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
        <button class="button button--primary" @click="apply(modal)"><?php i::_e('Aplicar') ?></button>
    </template>
</mc-modal>