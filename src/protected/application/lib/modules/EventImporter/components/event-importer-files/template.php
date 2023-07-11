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
                <h5 v-if="isProcessed(entity, file)"><?php i::_e('Processado') ?></h5>
                <h5 v-if="!isProcessed(entity, file)"><?php i::_e('Não processado') ?></h5>
            </div>
            <div>
                <div>
                    <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja processar este arquivo?") ?>" @confirm="processFile(file, entity)">
                        <template #button="modal">
                            <button @click="modal.open()" :class="['button','button--primary',{'disabled':isProcessed(entity, file)}]"><?= i::__("Processar") ?></button>
                        </template>
                    </mc-confirm-button>
                    <div>
                        <a class="button button--primary-outline" :href="file.url"><?= i::__("Baixar") ?></a>
                    </div>
                    <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja excluir este arquivo?") ?>" @confirm="file.delete()">
                        <template #button="modal">
                            <button @click="modal.open()" :class="['button','button--primary-outline',{'disabled':isProcessed(entity, file)}]"><?= i::__("Excluir") ?></button>
                        </template>
                    </mc-confirm-button>
                </div>
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