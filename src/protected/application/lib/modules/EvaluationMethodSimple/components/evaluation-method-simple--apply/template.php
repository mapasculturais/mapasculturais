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

<mc-modal :title="modalTitle" classes="create-modal create-app" button-label="<?php i::_e('Aplicar avaliações') ?>">

    <template #default>
        <div class="col-12">
            <div class="col-6">
                <label><?php i::_e('Avaliação') ?></label> <br>
                <select v-model="applyData.from">
                    <option value="all"><?php i::_e('Todos') ?></option>
                    <option v-for="item in consolidatedResults" :value="item.evaluation">{{valueToString(item.evaluation)}} ({{item.num}} <?php i::_e('Inscrições') ?>)</option>
                </select>
            </div>

            <div class="col-6">
                <label><?php i::_e('Status') ?></label> <br>
                <select v-model="applyData.to">
                    <option v-for="item in statusList" :value="item.status">{{item.label}}</option>
                </select>
            </div>
        </div>
        <div class="col-12">
            <label>
                <?php i::_e('Status') ?>
                <input type="checkbox" v-model="applyAll">
            </label>
        </div>
    </template>

    <template #actions="modal">
        <div class="col-12">
            <div class="col-6">
                <button class="button button--primary" @click="apply(modal)"><?php i::_e('Aplicar') ?></button>
            </div>
            <div class="col-6">
                <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
            </div>
        </div>
    </template>
</mc-modal>