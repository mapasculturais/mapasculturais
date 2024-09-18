<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-modal
    mc-tab
    mc-tabs
    mc-loading
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
            <mc-tab label="<?= i::esc_attr__('Por pontuação') ?>" slug='score'>
                <div class="grid-12 classification__panel">
                    <div class="col-12 apply-evaluations__range">
                        <Slider :step="-1" :tooltips="true" :max="maxResult" v-model="applyData.from"></Slider>
                    </div>

                    <div class="col-12 grid-12">
                        <div class="field field--horizontal col-6">
                            <label><?php i::_e('Nota mínima:') ?></label>
                            <input v-model.number.trim="applyData.from[0]" type="number" min="0" :max="maxResult" step="0.01" @input="validateValues()">
                        </div>
                        <div class="field field--horizontal col-6">
                            <label><?php i::_e('Nota maximo:') ?></label>
                            <input v-model.number.trim="applyData.from[1]" type="number" min="0" :max="maxResult" step="0.01" @input="validateValues()">
                        </div>
                    </div>

                    <div class="field col-12">
                        <label><?php i::_e('Selecione o status que deseja aplicar') ?></label>
                        <select v-model="applyData.setStatusTo">
                            <option v-for="item in statusList" :value="item.value">{{item.label}}</option>
                        </select>
                    </div>
                </div>
            </mc-tab>
            
            <mc-tab label="<?= i::esc_attr__('Por classificação') ?>" slug='classification'>
                <div class="grid-12 classification__panel">
                    <div class="field col-6">
                        <label>
                            <?php i::_e('Total de vagas:') ?>
                            <input type="number" v-model="vacancies">
                        </label>
                    </div>
                    <div class="field col-6">
                        <label>
                            <?php i::_e('Nota de corte:') ?>
                            <input type="number" v-model="cutoffScore">
                        </label>
                    </div>
                    <div class="field col-12">
                        <label>
                            <input type="checkbox" v-model="considerQuotas">
                            <?php i::_e('Considerar cotas') ?>
                        </label>
                    </div>
                    <div class="field col-12">
                        <label>
                            <input type="checkbox" value="earlyRegistrations" v-model="selectionType">
                            <?php i::_e('Selecionar as inscrições posicionadas na faixa de classificação') ?>
                        </label>
                    </div>
                    <div class="field col-12">
                        <label class="field__waitlist">
                            <input class="field__input" type="checkbox" value="waitList" v-model="selectionType">
                            <?php i::_e('Marcar como suplente as inscrições posicionadas fora da faixa de classificação que estejam acima da nota de corte') ?>
                        </label>
                    </div>

                    <div class="field col-12">
                        <label>
                            <input type="checkbox" value="invalidateRegistrations" v-model="selectionType">
                            <?php i::_e('Eliminar as inscrições com notas inferiores à nota de corte') ?>
                        </label>
                    </div>
                </div>
            </mc-tab>
        </mc-tabs>
    </template>

    <template v-if="!processing" #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close()"><?php i::_e('Cancelar') ?></button>
            <button class="button button--primary" @click="apply(modal)"><?php i::_e('Aplicar') ?></button>  
    </template>
    <template v-if="processing" #actions="modal">
        <mc-loading condition><?= i::__('aplicando avaliações') ?></mc-loading>
    </template>
</mc-modal>