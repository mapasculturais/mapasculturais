<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-modal
');
?>

<mc-modal :title="modalTitle" classes="apply-evaluations">

    <template #button="modal">
        <button class="button button--primary button--md" @click="modal.open()"><?php i::_e('Aplicar resultados das avaliações') ?></button>
    </template>

    <template #default>
        <div class="grid-12">
            <div class="field col-12">
                <label><?php i::_e('Avaliação') ?></label>
                <select v-model="applyData.from">
                    <option value="all"><?php i::_e('Todos') ?></option>
                    <option v-for="item in consolidatedResults" :value="item.evaluation">{{valueToString(item.evaluation)}} ({{item.num}} <?php i::_e('Inscrições') ?>)</option>
                </select>
            </div>

            <div class="field col-12">
                <label><?php i::_e('Status') ?></label>
                <select v-model="applyData.to">
                    <option v-for="item in statusList" :value="item.status">{{item.label}}</option>
                </select>
            </div>

            <h5 class="col-12"><?= i::__("Se você preferir não marcar a caixa abaixo, as avaliações serão aplicadas somente nas inscrições que com o status 'Pendente'.") ?></h5>

            <div class="field col-12">
                <label>
                    <input type="checkbox" v-model="applyAll">
                    <?php i::_e('Aplicar para todas as inscrições enviadas')?>
                </label>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
        <button class="button button--primary" @click="apply(modal)"><?php i::_e('Aplicar') ?></button>
    </template>
</mc-modal>