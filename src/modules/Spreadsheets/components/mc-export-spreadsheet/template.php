<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-accordion
    mc-loading
    mc-modal
    mc-alert
')
?>
<div class="mc-export-spreadsheet">
    <mc-modal title="Exportação de planilhas" @open="openModal()" @close="closeModal()">
        <template #default="modal">
            <div v-if="!processing" class="mc-export-spreadsheet__modal-content">
                <div class="mc-export-spreadsheet__buttons">
                    <button class="button button--primary" @click="exportSpreadsheet('csv', modal)"><?= i::__("Exportar como .csv") ?></button>
                    <button class="button button--primary" @click="exportSpreadsheet('xlsx', modal)"><?= i::__("Exportar como .xlsx") ?></button>
                    <button class="button button--primary" @click="exportSpreadsheet('ods', modal)"><?= i::__("Exportar como .ods") ?></button>
                </div>

                <mc-alert v-if="exportReturn" type="success" class="mc-export-spreadsheet__return">{{exportReturn}}</mc-alert>

                <mc-accordion v-if="showExportedFiles">
                    <template #title>
                        <h5 class="semibold"><?= i::__("Últimas exportações") ?></h5>
                    </template>
                    <template #content>
                        <div class="mc-export-spreadsheet__last-exports scrollbar">
                            <a v-for="exported in lastExported" class="mc-export-spreadsheet__link" :href="exported.url" download> <mc-icon name="download"></mc-icon> {{exported.name}} </a>
                        </div>
                    </template>
                </mc-accordion>
            </div>

            <mc-loading :condition="processing == 'exporting'"><?php i::_e('Exportando') ?></mc-loading>
        </template>

        <template #button="modal">
            <button class="button button--primary" @click="modal.open()"><?php i::_e('Exportar planilha') ?></button>
        </template>
    </mc-modal>
</div>