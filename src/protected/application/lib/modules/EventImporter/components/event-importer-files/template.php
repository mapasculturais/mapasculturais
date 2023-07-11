<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    event-importer-upload
    mc-entity
');
?>
<event-importer-upload></event-importer-upload>
<br>
<mc-entity type="agent" :id="global.auth.user.profile.id" select="files.event-import-file,event_importer_files_processed,event_importer_processed_file">
    <template #default="{entity}">
        <div v-for="file in getFiles(entity)">
            <div>
                {{file.name}}
            </div>
            <div>
                {{file.createTimestamp.date('numeric year')}} {{file.createTimestamp.time('2-digit')}}
            </div>
            <div>
                <?php i::_e('Não processado') ?>
            </div>
            <div>
                <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja processar este arquivo?") ?>" @confirm="processFile(file)"><?= i::__("Processar") ?></mc-confirm-button>
                <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja excluir este arquivo?") ?>" @confirm="file.delete()"><?= i::__("Excluir") ?></mc-confirm-button>
            </div>
            <div v-if="file.errors">
                <hr>
                <ul>
                    <li v-for="(errors,line) in file.errors">
                        <h5> <?= i::__("Erros encontrados na linha") ?> <strong>{{line}}</strong></h5>
                        <ul v-for="error in errors">
                            <li>{{error}}</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </template>
</mc-entity>