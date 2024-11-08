<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
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
                        <label class="input__label input__checkboxLabel input__multiselect" v-for="(item, index) in agents">
                            <input type="checkbox" v-model="item.value" /> {{ item.label }}
                        </label>
                    </div>
                </div>

                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Taxonomias dos agentes") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <label class="input__label input__checkboxLabel input__multiselect" v-for="(item, index) in taxonomiesAgents">
                            <input type="checkbox" v-model="item.value" /> {{ item.label }}
                        </label>
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
                        <label class="input__label input__checkboxLabel input__multiselect" v-for="(item, index) in spaces">
                            <input type="checkbox" v-model="item.value" /> {{ item.label }}
                        </label>
                    </div>
                </div>

                <div class="seal-locked-field__group">
                    <div class="seal-locked-field__group-title">
                        <h4 class="bold"><?php i::_e("Taxonomia dos espaços") ?></h4>
                    </div>
                    <div class="seal-locked-field__group-inputs">
                        <label class="input__label input__checkboxLabel input__multiselect" v-for="(item, index) in taxonomiesSpaces">
                            <input type="checkbox" v-model="item.value" /> {{ item.label }}
                        </label>
                    </div>
                </div>
            </div>

        </template>
    </mc-card>
</div>