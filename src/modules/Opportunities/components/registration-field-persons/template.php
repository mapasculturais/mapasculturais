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
<div class="registration-field-persons" :data-field="prop">
    <div class="registration-field-persons__list field">
        <label> {{ title }} </label>
        <small class="field__description"> {{ description }} </small>

        <div v-for="(person, index) in registration[prop]" class="registration-field-persons__person">

        <p class="semibold"> {{index + 1}}ª <?= i::__("Pessoa") ?> </p>

            <div class="registration-field-persons__person-fields grid-12">
                <div v-if="rules.name" class="field col-12" :class="[{'error': fieldError(person, 'name')}]">
                    <label>
                        <?= $this->text('nome', i::__('Como gostaria de ser chamado?')) ?>
                        <span v-if="isFieldRequired?.name" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-model="person.name" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.fullName" class="field col-12" :class="[{'error': fieldError(person, 'fullName')}]">
                    <label>
                        <?= $this->text('nome-completo', i::__('Nome completo')) ?>
                        <span v-if="isFieldRequired?.fullName" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-model="person.fullName" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.socialName" class="field col-12" :class="[{'error': fieldError(person, 'socialName')}]">
                    <label>
                        <?= $this->text('nome-social', i::__('Nome Social')) ?>
                        <span v-if="isFieldRequired?.socialName" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-model="person.socialName" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.cpf" class="field col-12" :class="[{'error': fieldError(person, 'cpf')}]">
                    <label>
                        <?= i::__('CPF:') ?>
                        <span v-if="isFieldRequired?.cpf" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-maska data-maska="###.###.###-##" v-model="person.cpf" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.income" class="field col-12" :class="[{'error': fieldError(person, 'income')}]">
                    <label>
                        <?= $this->text('renda', i::__('Renda individual em reais (calcular a renda média individual dos últimos três meses)')) ?>
                        <span v-if="isFieldRequired?.income" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.income" :options="income" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.education" class="field col-12" :class="[{'error': fieldError(person, 'education')}]">
                    <label>
                        <?= $this->text('escolaridade', i::__('Escolaridade')) ?>
                        <span v-if="isFieldRequired?.education" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.education" :options="education" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.telephone" class="field col-12" :class="[{'error': fieldError(person, 'telephone')}]">
                    <label>
                        <?= $this->text('telefone', i::__('Telefone do representante')) ?>
                        <span v-if="isFieldRequired?.telephone" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-maska data-maska="(##) #####-####" v-model="person.telephone" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.email" class="field col-12" :class="[{'error': fieldError(person, 'email')}]">
                    <label>
                        <?= $this->text('email', i::__('Email do representante')) ?>
                        <span v-if="isFieldRequired?.email" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <input type="text" v-model="person.email" @change="save()" :disabled="disabled" />
                </div>

                <div v-if="rules.race" class="field col-12" :class="[{'error': fieldError(person, 'race')}]">
                    <label>
                        <?= $this->text('raca-cor', i::__('Raça / Cor')) ?>
                        <span v-if="isFieldRequired?.race" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.race" :options="races" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.gender" class="field col-12" :class="[{'error': fieldError(person, 'gender')}]">
                    <label>
                        <?= $this->text('genero', i::__('Genero')) ?>
                        <span v-if="isFieldRequired?.gender" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.gender" :options="genders" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.sexualOrientation" class="field col-12" :class="[{'error': fieldError(person, 'sexualOrientation')}]">
                    <label>
                        <?= $this->text('orientacao-sexual', i::__('Orientação sexaul')) ?>
                        <span v-if="isFieldRequired?.sexualOrientation" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.sexualOrientation" :options="sexualOrientations" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.deficiencies" class="field col-12" :class="[{'error': fieldError(person, 'deficiencies')}]">
                    <label>
                        <?= $this->text('orientacao-sexual', i::__('Pessoa com deficiência - PDC?')) ?>
                        <span v-if="isFieldRequired?.deficiencies" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <div class="field__group">
                        <label v-for="deficiency in deficiencies" class="input__label input__checkboxLabel input__multiselect">
                            <input type="checkbox" :checked="person.deficiencies[deficiency]" v-model="person.deficiencies[deficiency]" @checked="save()" @change="save()" :disabled="disabled"/> <!-- :checked="" -->
                            <slot>{{deficiency}}</slot>
                        </label>
                    </div>
                </div>

                <div v-if="rules.comunty" class="field col-12" :class="[{'error': fieldError(person, 'community')}]">
                    <label>
                        <?= $this->text('comunidade', i::__('Considera-se pertencente a algum outro povo ou comunidade tradicional?')) ?>
                        <span v-if="isFieldRequired?.comunty" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="person.comunty" :options="communities" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="rules.area" class="field col-12" :class="[{'error': fieldError(person, 'area')}]">
                    <label>
                        <?= $this->text('areas', i::__('Quais são as 3 principais áreas de atuação do representante no campo artístico e cultural?')) ?>
                        <span v-if="isFieldRequired?.area" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-multiselect :model="person.area" title="<?php i::_e('Selecione as áreas de atuação') ?>" :items="areas" hide-filter hide-button @selected="save()" @removeed="save()" :disabled="disabled">
                        <template #default="{setFilter, popover}">
                            <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
                        </template>
                    </mc-multiselect>
                    <mc-tag-list editable :tags="person.area" classes="agent__background agent__color"></mc-tag-list>
                </div>

                <div v-if="rules.funcao" class="field col-12" :class="[{'error': fieldError(person, 'funcao')}]">
                    <label>
                        <?= $this->text('funcoes', i::__('Quais as 3 principais funções/profissões do representante no campo artístico e cultural?')) ?>
                        <span v-if="isFieldRequired?.funcao" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-multiselect :model="person.funcao" title="<?php i::_e('Selecione as áreas de atuação') ?>" :items="functions" hide-filter hide-button @selected="save()" @removeed="save()" :disabled="disabled">
                        <template #default="{setFilter, popover}">
                            <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as funções') ?>">
                        </template>
                    </mc-multiselect>
                    <mc-tag-list editable :tags="person.funcao" classes="agent__background agent__color"></mc-tag-list>
                </div>
            </div>

            <div class="registration-field-persons__person-action">
                <button v-if="!disabled" type="button" class="button button--sm button--icon button--text-danger" @click="removePerson(person)"><mc-icon name="trash"></mc-icon> <?= i::__("Remover pessoa") ?></button>
            </div>
        </div>

        <div class="registration-field-persons__add-person">
            <button v-if="rules.buttonText && !disabled" type="button" class="button button--sm button--icon button--primary" @click="addNewPerson()"><mc-icon name="add"></mc-icon> {{rules.buttonText}} </button>
            <button v-if="!rules.buttonText && !disabled" type="button" class="button button--sm button--icon button--primary" @click="addNewPerson()"><mc-icon name="add"></mc-icon> <?= i::__("Adicionar nova pessoa") ?> </button>
        </div>
    </div>
</div>