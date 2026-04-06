<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-data
    mc-tab
    mc-tabs
');
?>

<div class="registration-field-view">
    <mc-tabs sync-hash>
        <mc-tab v-for="(step, index) in stepTabs" :label="`${index + 1}. ${step.label}`" :slug="`ficha-${step.slug}`">
            <div class="registration-field-view__step">
                <ul class="attachment-list registration-field-view__list">
                    <li v-for="field in step.fields" class="attachment-list-item registration-view-mode registration-field-view__item">
                <template v-if="field.fieldType === 'section'">
                    <h4 class="registration-field-view__section-title">{{ field.title }}</h4>
                </template>

                <template v-else-if="field.fieldType === 'file'">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <a v-if="field.file" class="attachment-title" :href="field.file.url" target="_blank" rel="noopener noreferrer">{{ field.file.name }}</a>
                    <span v-else><em><?= i::__("Arquivo não enviado.") ?></em></span>
                </template>

                <template v-else-if="field.fieldType === 'persons'">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <template v-for="person in (phase[field.fieldName] || [])">
                        <div v-if="person" class="registration-field-view__person-card">
                            <div v-if="field.config?.name && person.name"><strong><?= i::__("Nome") ?>:</strong> {{ person.name }}</div>
                            <div v-if="field.config?.fullName && person.fullName"><strong><?= i::__("Nome completo") ?>:</strong> {{ person.fullName }}</div>
                            <div v-if="field.config?.socialName && person.socialName"><strong><?= i::__("Nome social") ?>:</strong> {{ person.socialName }}</div>
                            <div v-if="field.config?.cpf && person.cpf"><strong><?= i::__("CPF") ?>:</strong> {{ person.cpf }}</div>
                            <div v-if="field.config?.income && person.income"><strong><?= i::__("Renda") ?>:</strong> {{ person.income }}</div>
                            <div v-if="field.config?.education && person.education"><strong><?= i::__("Escolaridade") ?>:</strong> {{ person.education }}</div>
                            <div v-if="field.config?.telephone && person.telephone"><strong><?= i::__("Telefone") ?>:</strong> {{ person.telephone }}</div>
                            <div v-if="field.config?.email && person.email"><strong><?= i::__("Email") ?>:</strong> {{ person.email }}</div>
                            <div v-if="field.config?.race && person.race"><strong><?= i::__("Raça/Cor") ?>:</strong> {{ person.race }}</div>
                            <div v-if="field.config?.gender && person.gender"><strong><?= i::__("Gênero") ?>:</strong> {{ person.gender }}</div>
                            <div v-if="field.config?.sexualOrientation && person.sexualOrientation"><strong><?= i::__("Orientação sexual") ?>:</strong> {{ person.sexualOrientation }}</div>
                            <div v-if="field.config?.deficiencies && personValue(person, 'deficiencies')"><strong><?= i::__("Deficiências") ?>:</strong> {{ personValue(person, 'deficiencies') }}</div>
                            <div v-if="field.config?.comunty && person.comunty"><strong><?= i::__("Comunidade tradicional") ?>:</strong> {{ person.comunty }}</div>
                            <div v-if="field.config?.area && personValue(person, 'area')"><strong><?= i::__("Áreas de atuação") ?>:</strong> {{ personValue(person, 'area') }}</div>
                            <div v-if="field.config?.funcao && personValue(person, 'funcao')"><strong><?= i::__("Funções/Profissões") ?>:</strong> {{ personValue(person, 'funcao') }}</div>
                            <div v-if="field.config?.relationship && person.relationship"><strong><?= i::__("Relação") ?>:</strong> {{ person.relationship }}</div>
                            <div v-if="field.config?.function && person.function"><strong><?= i::__("Função") ?>:</strong> {{ person.function }}</div>
                        </div>
                    </template>
                </template>

                <template v-else-if="field.fieldType === 'custom-table'">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <div v-if="parseValue(phase[field.fieldName])?.length > 0">
                        <table class="custom-table-view registration-field-view__table">
                            <thead>
                                <tr>
                                    <th v-for="column in field.config?.columns">{{ column.name }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in parseValue(phase[field.fieldName])">
                                    <td v-for="(column, indexCol) in field.config?.columns">
                                        {{ row['col' + indexCol] || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else>
                        <em><?= i::__("Nenhum dado informado.") ?></em>
                    </div>
                </template>

                <template v-else-if="isLocationField(field)">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <div v-for="([key, item]) in locationEntries(phase[field.fieldName])">
                        <span v-if="key === 'address_level0'">
                            <strong v-if="item === 'BR'">{{ getAddressLabel(key, parseValue(phase[field.fieldName])?.address_level0) }}:</strong> {{ item }}
                        </span>
                        <span v-if="key !== 'address_level0' && key !== 'En_Pais'">
                            <strong>{{ getAddressLabel(key, parseValue(phase[field.fieldName])?.address_level0) }}:</strong> {{ item }}
                        </span>
                    </div>
                    <div v-if="parseValue(phase[field.fieldName])?.hasOwnProperty('publicLocation')">
                        <span>
                            <?= i::__("Este endereço pode ficar público na plataforma?:") ?>
                            {{ parseValue(phase[field.fieldName]).publicLocation == true || parseValue(phase[field.fieldName]).publicLocation == 'true' ? 'Sim' : 'Não' }}
                        </span>
                    </div>
                </template>

                <template v-else-if="isLinkField(field)">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <div v-for="(item, key) in parseValue(phase[field.fieldName])" v-if="item && key !== 'location' && key !== 'publicLocation'">
                        <b>{{ item.title }}:</b> <a target="_blank" rel="noopener noreferrer" :href="item.value">{{ item.value }}</a>
                    </div>
                </template>

                <template v-else-if="field.fieldType === 'bankFields'">
                    <label class="registration-field-view__label"><span v-if="field.required" class="registration-field-view__required">*</span> {{ field.title }}: </label>
                    <template v-if="hasValue(phase[field.fieldName])">
                        <p><strong><?= i::__("Típo de conta:") ?></strong> {{ bankData(phase[field.fieldName]).account_type }}</p>
                        <p><strong><?= i::__("Banco:") ?></strong> {{ bankData(phase[field.fieldName]).number }}</p>
                        <p><strong><?= i::__("Agencia:") ?></strong> {{ bankData(phase[field.fieldName]).branch }} - {{ bankData(phase[field.fieldName]).dv_branch }}</p>
                        <p><strong><?= i::__("Conta:") ?></strong> {{ bankData(phase[field.fieldName]).account_number }} - {{ bankData(phase[field.fieldName]).dv_account_number }}</p>
                    </template>
                    <span v-else><em><?= i::__("Campo não informado.") ?></em></span>
                </template>

                <template v-else>
                    <entity-data
                        :entity="phase"
                        :prop="field.fieldName"
                        :label="field.title"
                        show-required-mark
                        :field-required="!!field.required"
                    ></entity-data>
                </template>
                    </li>
                </ul>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
