<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-select
');
?>

<div class="geo-quota">
    <button v-if="!isActive" class="button button--primary button--icon" @click="open()">
        <mc-icon name="add"></mc-icon>
        <?= i::__('Adicionar cotas por território') ?>
    </button>

    <div v-if="isActive" class="geo-quota__card">
        <div class="geo-quota__header">
            <div class="geo-quota__title">
                <h4 class="bold"><?= i::__('Configuração de território') ?></h4>

                <button class="geo-quota__delete-button" @click="close()">
                    <mc-icon name="closed"></mc-icon>
                </button>
            </div>

            <div class="geo-quota__options">

                <div class="field geo-quota__field">
                    <label><?= i::__('Divisão territorial') ?></label>
                    <mc-select placeholder="Selecione uma divisão" :default-value="geoQuota.geoDivision" @change-option="setDivision"> <!-- :default-value="payment.status" @change-option="setPaymentStatus" -->
                        <option v-for="(division, index) in divisions" :key="index" :value="index">{{division.name}}</option>
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
                    <tr>
                        <th>Exemplo</th>
                        <td>
                            <div class="geo-quota__input-area">
                                <input class="geo-quota__input" type="number" /> %
                            </div>
                        </td>
                        <td>
                            <div class="geo-quota__input-area">
                                <input class="geo-quota__input" type="number" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table v-if="!geoQuota.geoDivision" class="geo-quota__table">
                <tbody>
                    <tr>
                        <th><?= i::__('Selecione uma divisão primeiro!') ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>