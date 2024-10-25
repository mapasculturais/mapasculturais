<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-select
');
?>
<div class="registration-field-persons">
    <div class="registration-field-persons__list">

        <div v-for="person in this.registration[this.prop]"  class="registration-field-persons__person">
            <div class="registration-field-persons__person-fields grid-12">
                <div class="field col-6 sm:col-12">
                    <label><?= i::__('Nome:') ?></label>
                    <input type="text" v-model="person.name" @change="save()" />
                </div>
    
                <div class="field col-6 sm:col-12">
                    <label><?= i::__('CPF:') ?></label>
                    <input type="text" v-maska data-maska="###.###.###-##" v-model="person.cpf" @change="save()" />
                </div>
                
                <div class="field col-6 sm:col-12">
                    <label><?= i::__('Função:') ?></label>
                    <input type="text"  v-model="person.function" @change="save()" />
                </div>
    
                <div class="field col-6 sm:col-12">
                    <label><?= i::__('Parentesco:') ?></label>
                    <mc-select v-model:default-value="person.relationship" @change-option="save()">
                        <option value="1"><?php i::_e("Cônjuge ou Companheiro(a)") ?></option>
                        <option value="2"><?php i::_e("Filho(a)") ?></option>
                        <option value="3"><?php i::_e("Enteado(a)") ?></option>
                        <option value="4"><?php i::_e("Neto(a) ou Bisneto(a)") ?></option>
                        <option value="5"><?php i::_e("Pai ou Mãe") ?></option>
                        <option value="6"><?php i::_e("Sogro(a)") ?></option>
                        <option value="7"><?php i::_e("Irmão ou Irmã") ?></option>
                        <option value="8"><?php i::_e("Genro ou Nora") ?></option>
                        <option value="9"><?php i::_e("Outro parente") ?></option>
                        <option value="10"><?php i::_e("Sem parentesco") ?></option>
                    </mc-select>
                </div>
            </div>

            <div class="registration-field-persons__person-action">
                <button type="button" class="button button--sm button--icon button--text-danger" @click="removePerson(person)"><mc-icon name="trash"></mc-icon> <?= i::__("Remover pessoa") ?></button>
            </div>
        </div>

        <div class="registration-field-persons__add-person">
            <button type="button" class="button button--sm button--icon button--primary" @click="addNewPerson()"><mc-icon name="add"></mc-icon> <?= i::__('Adicionar pessoa') ?></button>
        </div>
    </div>
</div>