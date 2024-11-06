<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-select
    mc-alert
');
?>

<div class="geo-quota">
    <mc-alert v-if="vacancies <= 0" type="warning" class="entity-owner-pending">
        <div>
            <?= i::__('O número de vagas do edital não foi configurado. Para definir a distribuição de vagas por território, é necessário primeiro estabelecer esse valor.') ?>
        </div>
    </mc-alert>

    <div v-if="!isActive" class="geo-quota__active">
        <button v-if="!isActive" class="button button--primary button--icon" @click="open()" :class="{'disabled' : vacancies <= 0}">
            <mc-icon name="add"></mc-icon>
            <?= i::__('Configurar distribuição de vagas por território') ?>
        </button>

    </div>

    <div v-if="isActive" class="geo-quota__card">
        <div class="geo-quota__header">
            <div class="geo-quota__title">
                <h4 class="bold"><?= i::__('Configuração de distribuição de vagas por território') ?></h4>

                <button class="button button--md button--text-danger button-icon" @click="trash()">
                    <mc-icon name="trash"></mc-icon>
                </button>
            </div>

            <mc-alert v-if="totalQuota > oppFirstPhase.vacancies" type="warning" class="entity-owner-pending">
                <div>
                    <?= i::__('O número de vagas distribuídas por território excede o total de vagas disponíveis no edital.') ?>
                </div>
            </mc-alert>

            <div class="geo-quota__options">
                <div class="field geo-quota__field" v-if="hasProponentType">
                    <template v-if="hasCollective">
                        <label><?= i::__('Campo que representa a localização do proponente Coletivo') ?>:</label>
                        <mc-select placeholder="<?= i::esc_attr__('Selecione um campo') ?>" :default-value="geoQuota?.fields?.Coletivo" @change-option="setGeoQuotaField($event, 'Coletivo')" show-filter>
                            <option v-for="(field, index) in getFields('Coletivo')" :key="index" :value="field.fieldName">{{ field.id ? '#' + field.id : '' }} {{ field.title }}</option>
                            <option value="geo"><?= i::__('Geolocalização do agente coletivo vinculado a inscrição') ?></option>
                        </mc-select>
                    </template>

                    <template v-if="hasMEI">
                        <label><?= i::__('Campo que representa a localização do proponente MEI') ?>:</label>
                        <mc-select placeholder="<?= i::esc_attr__('Selecione um campo') ?>" :default-value="geoQuota?.fields?.MEI" @change-option="setGeoQuotaField($event, 'MEI')" show-filter>
                            <option v-for="(field, index) in getFields('MEI')" :key="index" :value="field.fieldName">{{ field.id ? '#' + field.id : '' }} {{ field.title }}</option>
                            <option value="geo"><?= i::__('Geolocalização do agente responsável pela inscrição') ?></option>
                        </mc-select>
                    </template>

                    <template v-if="hasNaturalPerson">
                        <label><?= i::__('Campo que representa a localização do proponente Pessoa Física') ?>:</label>
                        <mc-select placeholder="<?= i::esc_attr__('Selecione um campo') ?>" :default-value="geoQuota?.fields?.['Pessoa Física']" @change-option="setGeoQuotaField($event, 'Pessoa Física')" show-filter>
                            <option v-for="(field, index) in getFields('Pessoa Física')" :key="index" :value="field.fieldName">{{ field.id ? '#' + field.id : '' }} {{ field.title }}</option>
                            <option value="geo"><?= i::__('Geolocalização do agente responsável pela inscrição') ?></option>
                        </mc-select>
                    </template>

                    <template v-if="hasLegalEntity">
                        <label><?= i::__('Campo que representa a localização do proponente Pessoa Jurídica') ?>:</label>
                        <mc-select placeholder="<?= i::esc_attr__('Selecione um campo') ?>" :default-value="geoQuota?.fields?.['Pessoa Jurídica']" @change-option="setGeoQuotaField($event, 'Pessoa Jurídica')" show-filter>
                            <option v-for="(field, index) in getFields('Pessoa Jurídica')" :key="index" :value="field.fieldName">{{ field.id ? '#' + field.id : '' }} {{ field.title }}</option>
                            <option value="geo"><?= i::__('Geolocalização do agente coletivo vinculado a inscrição') ?></option>
                        </mc-select>
                    </template>
                </div>

                <div class="field geo-quota__field" v-else>
                    <div>
                        <label><?= i::__('Campo que representa a localização do proponente') ?>:</label>
                        <mc-select placeholder="<?= i::esc_attr__('Selecione um campo') ?>" :default-value="geoQuota?.fields?.['default']" @change-option="setGeoQuotaField($event, 'default')" show-filter>
                            <option v-for="(field, index) in getFields()" :key="index" :value="field.fieldName">{{ field.id ? '#' + field.id : '' }} {{ field.title }}</option>
                            <option value="geo"><?= i::__('Geolocalização do agente responsável pela inscrição') ?></option>
                        </mc-select>
                    </div>
                </div>
            </div>

            <div class="geo-quota__options">

                <div class="field geo-quota__field">
                    <label><?= i::__('Divisão territorial') ?></label>
                    <mc-select placeholder="Selecione uma divisão" :default-value="geoQuota.geoDivision" @change-option="setDivision" show-filter> <!-- :default-value="payment.status" @change-option="setPaymentStatus" -->
                        <option v-for="(division, index) in divisions" :key="index" :value="division.metakey">{{division.name}}</option>
                    </mc-select>
                </div>

                <span v-if="geoQuota.geoDivision" class="geo-quota__description">
                    <?= i::__("Na ausência de valores em alguma das regiões, o saldo resultante será distribuído igualmente nos campos vazios") ?>
                </span>
            </div>
        </div>

        <div class="geo-quota__content">
            <table v-if="geoQuota.geoDivision" class="geo-quota__table">
                <thead>
                    <tr>
                        <td></td>
                        <td><?= i::__('Porcentagem') ?></td>
                        <td><?= i::__('Número de vagas') ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(value, option) in geoQuota.distribution">
                        <th>{{option}}</th>
                        <td>
                            <div class="geo-quota__input-area">
                                <input class="geo-quota__input" :value="getPercentage(option)" type="number" @change="setPercentage(option, $event); sumGeoQuota(option)" /> %
                            </div>
                        </td>
                        <td>
                            <div class="geo-quota__input-area">
                                <input class="geo-quota__input" v-model="geoQuota.distribution[option]" type="number" @input="sumGeoQuota(option)"/>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table v-if="!geoQuota.geoDivision" class="geo-quota__table">
                <tbody>
                    <tr>
                        <th><?= i::__('Selecione uma divisão geográfica primeiro!') ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>