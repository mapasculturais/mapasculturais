<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-accordion
')
?>
<div class="mc-export-spreadsheet">
    <mc-modal title="Exportação de planilhas">
        <template #default>

            <div class="mc-export-spreadsheet__modal-content">
                <div class="mc-export-spreadsheet__buttons">
                    <button class="button button--primary" @click="exportSpreadsheet('csv')"><?= i::__("Baixar csv") ?></button>
                    <button class="button button--primary" @click="exportSpreadsheet('xlsx')"><?= i::__("Baixar xlsx") ?></button>
                    <button class="button button--primary" @click="exportSpreadsheet('ods')"><?= i::__("Baixar ods") ?></button>
                </div>

                <mc-accordion v-if="showExportedFiles">
                    <template #title>
                        <h5 class="semibold"><?= i::__("Últimas exportações") ?></h5>
                    </template>
                    <template #content>
                        <div class="mc-export-spreadsheet__last-exports">
                            <a v-for="exported in lastExported"> {{exported}} </a>
                        </div>
                    </template>
                </mc-accordion>
            </div>

        </template>

        <template #button="modal">
            <button class="button button--primary" @click="modal.open()"><?php i::_e('Exportar planilha') ?></button>
        </template>
    </mc-modal>
</div>