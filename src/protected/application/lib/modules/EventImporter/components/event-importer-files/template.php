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
<div class="grid-12 event-importer-files">
    <p class="semibold col-12">
        <?= i::__("Neste espaço você pode importar eventos a partir do preenchimento da planilha modelo. Para isso, baixe a planilha, complete com os dados do seu evento, importe a planilha no botão abaixo e, por fim, ative a integração no botão Processar")?>
    </p>

    <event-importer-upload classes="col-12"></event-importer-upload>
    
    <mc-entity type="agent" :id="global.auth.user.profile.id" select="files.event-import-file,event_importer_files_processed,event_importer_processed_file">
        <template #default="{entity}">
            <mc-card class="col-12 event-importer-files__card" v-for="file in getFiles(entity)">
                <div class="event-importer-files__card-content">
                    <div class="event-importer-files__name">
                        <p class="semibold uppercase"><?= i::__("Nome do arquivo") ?></p>
                        <p class="bold">{{file.name}}</p>
                    </div>
                    <div class="event-importer-files__date">
                        <p class="semibold uppercase"><?= i::__("Data de importação") ?></p>
                        <p class="bold">{{file.createTimestamp.date('numeric year')}} <?= i::__("às") ?> {{file.createTimestamp.time('2-digit')}}</p>
                    </div>
                </div>

                <div class="event-importer-files__card-footer">

                    <div class="event-importer-files__status">
                        <h5 v-if="isProcessed(entity, file)"><?php i::_e('Processado em') ?> {{processedDate(entity, file)}}</h5>
                        <h5 v-if="!isProcessed(entity, file)"><?php i::_e('Não processado') ?></h5>
                    </div>

                    <div class="event-importer-files__buttons">
                        <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja processar este arquivo?") ?>" @confirm="processFile(file, entity)">
                            <template #button="modal">
                                <button @click="modal.open()" :class="['button','button--primary',{'disabled':isProcessed(entity, file)}]">
                                    <span v-if="isProcessed(entity, file)"><?= i::__("Processado") ?></span>
                                    <span v-if="!isProcessed(entity, file)"><?= i::__("Processar") ?></span>
                                </button>
                            </template>
                        </mc-confirm-button>

                        <a class="button button--primary-outline" :href="file.url"><?= i::__("Baixar") ?></a>
                        
                        <mc-confirm-button message="<?= i::esc_attr__("Você está certo que deseja excluir este arquivo?") ?>" @confirm="file.delete()">
                            <template #button="modal">
                                <button @click="modal.open()" :class="['button','button--primary-outline',{'disabled':isProcessed(entity, file)}]"><?= i::__("Excluir") ?></button>
                            </template>
                        </mc-confirm-button>
                    </div>

                    <div v-if="file.errors">
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
            </mc-card>
        </template>
    </mc-entity>
</div>