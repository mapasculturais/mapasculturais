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
<div :class="classes">
    <div class="seal-locked-field__title">
        <h4><?php i::_e("Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo") ?></h4>
    </div>

    <mc-card class="seal-locked-field__card">
        <template #title>
            <h3><?php i::_e("Agentes") ?></h3>
        </template>
        <template #content>
            <div class="seal-locked-field__container">
                <div class="grid-12">
                    <div v-for="(item, index) in agents" class="sm:col-6 col-3">
                        <input type="checkbox" v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
            <div class="seal-locked-field__container">
                <h5><?php i::_e("Taxonomias") ?></h5>
                <div class="grid-12">
                    <div v-for="(item, index) in taxonomiesAgents" class="sm:col-6 col-3">
                        <input type="checkbox" v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
        </template>
    </mc-card>

    <mc-card class="seal-locked-field__card">
        <template #title>
            <h3><?php i::_e("Espaços") ?></h3>
        </template>

        <template #content>
            <div class="seal-locked-field__container">
                <div class="grid-12">
                    <div class="col-12">
                        <div class="grid-12">
                            <div v-for="(item, index) in spaces" class="sm:col-6 col-3">
                                <input type="checkbox"  v-model="item.value" /> {{ item.label }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <h5><?php i::_e("Taxonomias") ?></h5>
                        <div class="grid-12">
                            <div v-for="(item, index) in taxonomiesSpaces" class="sm:col-6 col-3">
                                <input type="checkbox" v-model="item.value" /> {{ item.label }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </mc-card>
</div>