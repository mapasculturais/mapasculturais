<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-modal
    mc-tab
    mc-tabs
');
?>

<mc-modal title="<?= i::esc_attr__('Aplicar resultados das avaliações') ?>" classes="apply-evaluations" @close="modalClose()">

    <template #button="modal">
        <button class="button button--primary button--icon" @click="modal.open()">
            <mc-icon name="add"></mc-icon>
            <?php i::_e('Aplicar resultados das avaliações') ?>
        </button>
    </template>

    <template #default>
        <mc-tabs @changed="changed($event)">
            <mc-tab label="<?= i::esc_attr__('Por resultado') ?>" slug="result">
                <div class="grid-12 classification__panel">
                    <div class="field col-12">
                        <label for="evaluation-simple-result"><?php i::_e('Selecione as avaliações') ?></label>
                        <select id="evaluation-simple-result" v-model="applyData.from">
                            <option value="all"><?php i::_e('Todos') ?></option>
                            <option v-for="item in consolidatedResults" :value="item.evaluation">{{valueToString(item.evaluation)}} ({{item.num}} <?php i::_e('Inscrições') ?>)</option>
                        </select>
                    </div>

                    <div class="field col-12">
                        <label for="evaluation-simple-result-status"><?php i::_e('Selecione o status que deseja aplicar') ?></label>
                        <select id="evaluation-simple-result-status" v-model="applyData.to">
                            <option v-for="item in statusList" :value="item.status">{{item.label}}</option>
                        </select>
                    </div>

                    <div class="apply-evaluations__apply-all col-12">
                        <h5>
                            <?= i::__("Se você preferir não marcar a caixa abaixo, as avaliações serão aplicadas somente ") ?> <span class="semibold"><?=i::__("nas inscrições que com o status 'Pendente'.")?></span>
                        </h5>

                        <div class="field">
                            <label>
                                <input type="checkbox" v-model="applyAll">
                                <?php i::_e('Aplicar para todas as inscrições enviadas') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr__('Por inscrição') ?>" slug="registration">
                <div class="grid-12 classification__panel">
                    <div class="field col-12">
                        <label for="evaluation-simple-registration-list"><?php i::_e('Lista de inscrições') ?></label>
                        <div class="field opportunity-evaluation-committee__registration-list-textarea">
                            <textarea id="evaluation-simple-registration-list" v-model="registrationListText" placeholder="<?= i::esc_attr__('Preencha com os números das inscrições em que deseja aplicar o resultado das avaliações, separados por vírgula.') ?>" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="field col-12">
                        <label for="evaluation-simple-registration-status"><?php i::_e('Selecione o status que deseja aplicar') ?></label>
                        <select id="evaluation-simple-registration-status" v-model="applyData.to">
                            <option v-for="item in statusList" :value="item.status">{{item.label}}</option>
                        </select>
                    </div>
                </div>
            </mc-tab>
        </mc-tabs>
    </template>

    <template #actions="modal">
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
        <button class="button button--primary" @click="apply(modal)"><?php i::_e('Aplicar') ?></button>
    </template>
</mc-modal>
