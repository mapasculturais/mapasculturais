<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-link
');
?>
<div class="entity-table">

    <div class="opportunity-registration-table__filter">
        <div class="opportunity-registration-table__search-key">
            <input type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
            <button @click="search()" class="opportunity-registration-table__search-button">
                <mc-icon name="search"></mc-icon>
            </button>
        </div>
        <div class="opportunity-registration-table__search-fields">
            <h4 class="bold"><?= i::__('Filtrar:') ?></h4>
            <div class="field"><input type="number" /></div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Operador:') ?></span></option>
                </select>
            </div>
            <div class="field"><input type="number"></div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Status de inscrição:') ?></span></option>
                </select>
            </div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Exequibilidade (R$)') ?></span></option>
                </select>
            </div>
        </div>
        <!-- <mc-tag-list class="opportunity-registration-table__taglists"></mc-tag-list> -->
        <div class="field opportunity-registration-table__select-tag">
            <select>
                <option value=""><span><?= i::__('Colunas habilitadas na tabela:') ?></span></option>
            </select>
        </div>
    </div>
    <ul>
        <template v-for="header in headers" :key="header.value">
            <li v-if="!header.required">
                <button type="button" @click="toggleColumn(header)">
                    {{header.text}}

                </button>
            </li>
        </template>
    </ul>
    <!-- :body-row-class-name="customRowClassName" -->
    {{itemsSelected}}
    <EasyDataTable :headers="activeHeaders" table-class-name="entity-table__table" v-model:items-selected="itemsSelected" :items="items" rows-per-page-message="<?= i::esc_attr__('linhas por página') ?>">
        <template v-for="slot in activeSlots" #[slot]="item">
            <slot :name="slot" v-bind="item" ></slot>
        </template>

        <template #item-open="{id}">
            <mc-link class="button button--primary" :params="[id]" route="registration/single"><?= i::esc_attr__('Conferir inscrição') ?></mc-link>
        </template>

        <template #item-option="{option}">
            <select v-model="option">
                <option v-for="options in option" :value="options">{{options}}</option>
            </select>
        </template>
    </EasyDataTable>
</div>