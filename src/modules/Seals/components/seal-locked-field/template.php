<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-select
    mc-confirm-button
');
?>
<div class="seal-locked-field">
    <div class="seal-locked-field__title">
        <h3><?php i::_e("Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo") ?></h3>
    </div>

    <mc-card>
        <template #title>
            <h3><?= i::__("Agentes") ?></h3>
        </template>
        <template #content>

            <div class="seal-locked-field__groups">
                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Campos dos agentes") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <div class="seal-locked-field__item" v-for="(item, index) in agents" :key="item.uid">
                            <label class="input__label input__checkboxLabel input__multiselect">
                                <input type="checkbox" v-model="item.value" @change="onFieldChange(item)" /> {{ item.label }}
                            </label>

                            <div class="seal-locked-field__item-config" v-if="item.value">
                                <label class="input__label input__checkboxLabel">
                                    <input type="checkbox" v-model="item.hasExpiry" @change="onHasExpiryChange(item)" />
                                    {{ text('hasExpiryLabel') }}
                                </label>

                                <div class="seal-locked-field__expiry-fields" v-if="item.hasExpiry">
                                    <label class="seal-locked-field__field" :for="'period-value-' + item.uid">
                                        <span>{{ text('periodValueLabel') }}</span>
                                        <input :id="'period-value-' + item.uid" type="number" v-model="item.periodValue" min="1" @change="onPeriodValueChange(item)" />
                                    </label>

                                    <!-- mc-select não expõe props id/aria-labelledby; o label envolve o controle para manter a associação programática -->
                                    <label class="seal-locked-field__field">
                                        <span>{{ text('periodUnitLabel') }}</span>
                                        <mc-select :default-value="item.periodUnit" @change-option="onPeriodUnitChange(item, $event)">
                                            <option value="day">{{ text('day') }}</option>
                                            <option value="month">{{ text('month') }}</option>
                                            <option value="year">{{ text('year') }}</option>
                                        </mc-select>
                                    </label>
                                </div>

                                <mc-confirm-button @confirm="confirmIsInvalidator" @cancel="cancelIsInvalidator">
                                    <template #button="{open}">
                                        <label class="input__label input__checkboxLabel">
                                            <input type="checkbox" :checked="item.isInvalidator" @click="onIsInvalidatorChange($event, item, open)" :disabled="!item.hasExpiry" />
                                            {{ text('isInvalidatorLabel') }}
                                        </label>
                                    </template>
                                    <template #message="{confirm, cancel}">
                                        <div role="alertdialog" aria-modal="true" aria-live="assertive" :aria-describedby="'seal-locked-field-invalidator-message-' + item.uid" :aria-labelledby="'seal-locked-field-invalidator-title-' + item.uid">
                                            <h4 :id="'seal-locked-field-invalidator-title-' + item.uid" class="bold">{{ text('isInvalidatorConfirmTitle') }}</h4>
                                            <p :id="'seal-locked-field-invalidator-message-' + item.uid">{{ text('isInvalidatorConfirmMessage') }}</p>
                                        </div>
                                    </template>
                                </mc-confirm-button>

                                <p class="seal-locked-field__help-text">{{ text('invalidatorHelpText') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Taxonomias dos agentes") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <div class="seal-locked-field__item" v-for="(item, index) in taxonomiesAgents" :key="item.uid">
                            <label class="input__label input__checkboxLabel input__multiselect">
                                <input type="checkbox" v-model="item.value" @change="onFieldChange(item)" /> {{ item.label }}
                            </label>

                            <div class="seal-locked-field__item-config" v-if="item.value">
                                <label class="input__label input__checkboxLabel">
                                    <input type="checkbox" v-model="item.hasExpiry" @change="onHasExpiryChange(item)" />
                                    {{ text('hasExpiryLabel') }}
                                </label>

                                <div class="seal-locked-field__expiry-fields" v-if="item.hasExpiry">
                                    <label class="seal-locked-field__field" :for="'period-value-' + item.uid">
                                        <span>{{ text('periodValueLabel') }}</span>
                                        <input :id="'period-value-' + item.uid" type="number" v-model="item.periodValue" min="1" @change="onPeriodValueChange(item)" />
                                    </label>

                                    <!-- mc-select não expõe props id/aria-labelledby; o label envolve o controle para manter a associação programática -->
                                    <label class="seal-locked-field__field">
                                        <span>{{ text('periodUnitLabel') }}</span>
                                        <mc-select :default-value="item.periodUnit" @change-option="onPeriodUnitChange(item, $event)">
                                            <option value="day">{{ text('day') }}</option>
                                            <option value="month">{{ text('month') }}</option>
                                            <option value="year">{{ text('year') }}</option>
                                        </mc-select>
                                    </label>
                                </div>

                                <mc-confirm-button @confirm="confirmIsInvalidator" @cancel="cancelIsInvalidator">
                                    <template #button="{open}">
                                        <label class="input__label input__checkboxLabel">
                                            <input type="checkbox" :checked="item.isInvalidator" @click="onIsInvalidatorChange($event, item, open)" :disabled="!item.hasExpiry" />
                                            {{ text('isInvalidatorLabel') }}
                                        </label>
                                    </template>
                                    <template #message="{confirm, cancel}">
                                        <div role="alertdialog" aria-modal="true" aria-live="assertive" :aria-describedby="'seal-locked-field-invalidator-message-' + item.uid" :aria-labelledby="'seal-locked-field-invalidator-title-' + item.uid">
                                            <h4 :id="'seal-locked-field-invalidator-title-' + item.uid" class="bold">{{ text('isInvalidatorConfirmTitle') }}</h4>
                                            <p :id="'seal-locked-field-invalidator-message-' + item.uid">{{ text('isInvalidatorConfirmMessage') }}</p>
                                        </div>
                                    </template>
                                </mc-confirm-button>

                                <p class="seal-locked-field__help-text">{{ text('invalidatorHelpText') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </template>
    </mc-card>

    <mc-card>
        <template #title>
            <h3><?php i::_e("Espaços") ?></h3>
        </template>
        <template #content>

            <div class="seal-locked-field__groups">
                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Campos dos espaços") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <div class="seal-locked-field__item" v-for="(item, index) in spaces" :key="item.uid">
                            <label class="input__label input__checkboxLabel input__multiselect">
                                <input type="checkbox" v-model="item.value" @change="onFieldChange(item)" /> {{ item.label }}
                            </label>

                            <div class="seal-locked-field__item-config" v-if="item.value">
                                <label class="input__label input__checkboxLabel">
                                    <input type="checkbox" v-model="item.hasExpiry" @change="onHasExpiryChange(item)" />
                                    {{ text('hasExpiryLabel') }}
                                </label>

                                <div class="seal-locked-field__expiry-fields" v-if="item.hasExpiry">
                                    <label class="seal-locked-field__field" :for="'period-value-' + item.uid">
                                        <span>{{ text('periodValueLabel') }}</span>
                                        <input :id="'period-value-' + item.uid" type="number" v-model="item.periodValue" min="1" @change="onPeriodValueChange(item)" />
                                    </label>

                                    <!-- mc-select não expõe props id/aria-labelledby; o label envolve o controle para manter a associação programática -->
                                    <label class="seal-locked-field__field">
                                        <span>{{ text('periodUnitLabel') }}</span>
                                        <mc-select :default-value="item.periodUnit" @change-option="onPeriodUnitChange(item, $event)">
                                            <option value="day">{{ text('day') }}</option>
                                            <option value="month">{{ text('month') }}</option>
                                            <option value="year">{{ text('year') }}</option>
                                        </mc-select>
                                    </label>
                                </div>

                                <mc-confirm-button @confirm="confirmIsInvalidator" @cancel="cancelIsInvalidator">
                                    <template #button="{open}">
                                        <label class="input__label input__checkboxLabel">
                                            <input type="checkbox" :checked="item.isInvalidator" @click="onIsInvalidatorChange($event, item, open)" :disabled="!item.hasExpiry" />
                                            {{ text('isInvalidatorLabel') }}
                                        </label>
                                    </template>
                                    <template #message="{confirm, cancel}">
                                        <div role="alertdialog" aria-modal="true" aria-live="assertive" :aria-describedby="'seal-locked-field-invalidator-message-' + item.uid" :aria-labelledby="'seal-locked-field-invalidator-title-' + item.uid">
                                            <h4 :id="'seal-locked-field-invalidator-title-' + item.uid" class="bold">{{ text('isInvalidatorConfirmTitle') }}</h4>
                                            <p :id="'seal-locked-field-invalidator-message-' + item.uid">{{ text('isInvalidatorConfirmMessage') }}</p>
                                        </div>
                                    </template>
                                </mc-confirm-button>

                                <p class="seal-locked-field__help-text">{{ text('invalidatorHelpText') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Taxonomia dos espaços") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <div class="seal-locked-field__item" v-for="(item, index) in taxonomiesSpaces" :key="item.uid">
                            <label class="input__label input__checkboxLabel input__multiselect">
                                <input type="checkbox" v-model="item.value" @change="onFieldChange(item)" /> {{ item.label }}
                            </label>

                            <div class="seal-locked-field__item-config" v-if="item.value">
                                <label class="input__label input__checkboxLabel">
                                    <input type="checkbox" v-model="item.hasExpiry" @change="onHasExpiryChange(item)" />
                                    {{ text('hasExpiryLabel') }}
                                </label>

                                <div class="seal-locked-field__expiry-fields" v-if="item.hasExpiry">
                                    <label class="seal-locked-field__field" :for="'period-value-' + item.uid">
                                        <span>{{ text('periodValueLabel') }}</span>
                                        <input :id="'period-value-' + item.uid" type="number" v-model="item.periodValue" min="1" @change="onPeriodValueChange(item)" />
                                    </label>

                                    <!-- mc-select não expõe props id/aria-labelledby; o label envolve o controle para manter a associação programática -->
                                    <label class="seal-locked-field__field">
                                        <span>{{ text('periodUnitLabel') }}</span>
                                        <mc-select :default-value="item.periodUnit" @change-option="onPeriodUnitChange(item, $event)">
                                            <option value="day">{{ text('day') }}</option>
                                            <option value="month">{{ text('month') }}</option>
                                            <option value="year">{{ text('year') }}</option>
                                        </mc-select>
                                    </label>
                                </div>

                                <mc-confirm-button @confirm="confirmIsInvalidator" @cancel="cancelIsInvalidator">
                                    <template #button="{open}">
                                        <label class="input__label input__checkboxLabel">
                                            <input type="checkbox" :checked="item.isInvalidator" @click="onIsInvalidatorChange($event, item, open)" :disabled="!item.hasExpiry" />
                                            {{ text('isInvalidatorLabel') }}
                                        </label>
                                    </template>
                                    <template #message="{confirm, cancel}">
                                        <div role="alertdialog" aria-modal="true" aria-live="assertive" :aria-describedby="'seal-locked-field-invalidator-message-' + item.uid" :aria-labelledby="'seal-locked-field-invalidator-title-' + item.uid">
                                            <h4 :id="'seal-locked-field-invalidator-title-' + item.uid" class="bold">{{ text('isInvalidatorConfirmTitle') }}</h4>
                                            <p :id="'seal-locked-field-invalidator-message-' + item.uid">{{ text('isInvalidatorConfirmMessage') }}</p>
                                        </div>
                                    </template>
                                </mc-confirm-button>

                                <p class="seal-locked-field__help-text">{{ text('invalidatorHelpText') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </template>
    </mc-card>
</div>
