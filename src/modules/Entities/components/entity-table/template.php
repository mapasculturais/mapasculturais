<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-link
    mc-multiselect
    mc-entities
    mc-tag-list
');
?>
<div class="entity-table">
    <mc-entities :select="select" :type="type" :query="query">
        <template #header>
            <div class="opportunity-registration-table__filter">
                <div class="opportunity-registration-table__search-key">
                    <input v-model="searchText" @keyup="search(searchText)" type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
                    <button @click="search(searchText)" class="opportunity-registration-table__search-button">
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
                            <option value=""><?= i::__('Status da inscrição') ?></option>
                            <option value="-10"><?= i::__('Lixeira') ?></option>
                            <option value="-2"><?= i::__('Arquivado') ?></option>
                            <option value="-9"><?= i::__('Não habilitada') ?></option>
                            <option value="0"><?= i::__('Em rascunho') ?></option>
                            <option value="1"><?= i::__('Enviada') ?></option>
                            <option value="2"><?= i::__('Inválida') ?></option>
                            <option value="3"><?= i::__('Não aprovada') ?></option>
                            <option value="8"><?= i::__('Suplente') ?></option>
                            <option value="10"><?= i::__('Aprovada:') ?></option>
                        </select>
                    </div>

                    <div class="field">
                        <select>
                            <option value=""><span><?= i::__('Exequibilidade (R$)') ?></span></option>
                        </select>
                    </div>
                </div>
                <div class="field opportunity-registration-table__select-tag">

                    <mc-multiselect #default="{setFilter, popover}" @selected="addInColumns" @removed="removeFromColumns" :model="selectedColumns" :items="optionalHeaders" hide-filter hide-button>
                        <input @input="addInColumns($event.target.value)" class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Colunas habilitadas na tabela') ?>">
                    </mc-multiselect>

                    <mc-tag-list editable class="opportunity-registration-table__taglists" classes="opportunity__background" :tags="selectedColumns" @remove="removeFromColumns"></mc-tag-list>
                </div>
            </div>
        </template>

        <template #default="{entities}">
            <div>
                <table>
                    <thead>
                        <tr>
                            <template v-for="header in activeHeaders">
                                <th>{{header.text}}</th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in entities">
                            <template v-for="header in activeHeaders">
                                <td>
                                    <slot :name="parseSlug(header)" v-bind="item">
                                        {{getEntityData(item, header.value)}}
                                    </slot>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </mc-entities>
</div>