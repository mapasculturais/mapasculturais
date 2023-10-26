<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-popover
');
$entity = $this->controller->requestedEntity;
$url = $app->createUrl('opportunity', 'importFields',[$entity->id]);
?>
<div class="opportunity-form-import" :class="classes">
    <div class="import-buttons">
        <mc-popover openside="down-right" classes="opportunity-form-import--popover">
            <template #button="popover">
                <slot name="button">
                    <button type="button" @click="popover.toggle()" class="import-buttons__import button">
                        <?php i::_e("Importar Formulário") ?>
                    </button>
                </slot>
            </template>

            <template #default="{popover, close}">
                <form name="importFields" class="entity-files__newFile import-opp" method="post" action="<?= $url ?>" enctype="multipart/form-data">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="field header-import">
                                <h4 class="header-import__title"><?php i::_e('Importe o arquivo de formulário') ?></h4>
                                <label class="header-import__label"><?php i::_e('O arquivo importado deve estar no formato .txt ') ?></label>
                                <div class="field">
                                    <input type="file" name="fieldsFile" @change="setFile" ref="file" accept=".txt">
                                    <small>Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
                                </div>
                            </div>
                        </div>

                        <button class="btn__cancel col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                        <button class="btn__confirm col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                    </div>
                </form>
            </template>
        </mc-popover>
    </div>

</div>