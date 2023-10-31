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
<ul>
    <template  v-for="header in headers" :key="header.value">
        <li v-if="!header.required">
            <button type="button" @click="toggleColumn(header)">
                {{header.text}}
            </button>
        </li>
    </template>
</ul>
<EasyDataTable :headers="activeHeaders" :body-row-class-name="customRowClassName" :items="items" rows-per-page-message="<?= i::esc_attr__('linhas por página')?>">
</EasyDataTable>