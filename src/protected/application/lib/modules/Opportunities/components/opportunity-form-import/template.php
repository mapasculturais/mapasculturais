<?php

use MapasCulturais\i;

$this->import('
mapas-container
popover
modal
    
');
?>

<div class="opportunity-form-import">
    <div class="import-buttons">

        <popover openside="down-right" classes="opportunity-form-import--popover">
            <template #button="popover">
                <slot name="button">
                    <button type="button" @click="popover.toggle()" class="import-buttons__import button">
                        <?php i::_e("Importar Formulário") ?>
                    </button>
                </slot>
            </template>

            <template #default="{popover, close}">
                <form @submit="upload(popover); $event.preventDefault();" class="entity-files__newFile import-opp">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="field header-import">
                                <h4 class="header-import__title"><?php i::_e('Importe o arquivo de formulário') ?></h4>
                                <label class="header-import__label"><?php i::_e('O arquivo importado deve estar no formato .txt ') ?></label>
                                <input type="file" @change="setFile" ref="file" accept=".txt">
                            </div>
                        </div>

                        <button class="btn__cancel col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                        <button class="btn__confirm col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                    </div>
                </form>
            </template>
        </popover>
    </div>

</div>