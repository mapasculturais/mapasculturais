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
<ul>
    <template  v-for="header in headers" :key="header.value">
        <li v-if="!header.required">
            <button type="button" @click="toggleColumn(header)">
                {{header.text}}
                
            </button>
        </li>
    </template>
</ul>
<EasyDataTable :headers="activeHeaders" table-class-name="entity-table__table" :body-row-class-name="customRowClassName"  :items="items" rows-per-page-message="<?= i::esc_attr__('linhas por página')?>">
    <template #item-open="{id}">
        <mc-link class="button button--primary" :params="id" route="registration/single"><?= i::esc_attr__('Conferir inscrição')?></mc-link>
    </template>

    <template #item-option="{option}">
        <select v-model="option">
            <option v-for="options in option" :value="options">{{options}}</option> 
        </select>
    </template>
<slot name="buton">

</slot>
</EasyDataTable>