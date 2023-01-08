<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    select-entity
    mapas-card
');
?>

<div :class="classes">
    <div class="form_block_field__title">
        <h4><?php i::_e("Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo") ?></h4>
    </div>

    <mapas-card class="form_block_field__card">
        <template #title>
            <h3><?php i::_e("Agentes") ?></h3>
        </template>
        <template #content>
            <div class="form_block_field__container">
                <div class="grid-12">
                    <div v-for="(item, index) in agents" class="col-3">
                        <input type="checkbox" v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
            <div class="form_block_field__container">
                <h5><?php i::_e("Taxonomias") ?></h5>
                <div class="grid-12">
                    <div v-for="(item, index) in taxonomiesAgents" class="col-3">
                        <input type="checkbox" v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
        </template>
    </mapas-card>

    <mapas-card class="form_block_field__card">
        <template #title>
            <h3><?php i::_e("Espaços") ?></h3>
        </template>

        <template #content>
            <div class="form_block_field__container">
                <div class="grid-12">
                    <div v-for="(item, index) in spaces" class="col-3">
                        <input type="checkbox"  v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
            <div class="form_block_field__container">
                <h5><?php i::_e("Taxonomias") ?></h5>
                <div class="grid-12">
                    <div v-for="(item, index) in taxonomiesSpaces" class="col-3">
                        <input type="checkbox" v-model="item.value" /> {{ item.label }}
                    </div>
                </div>
            </div>
        </template>
    </mapas-card>
</div>