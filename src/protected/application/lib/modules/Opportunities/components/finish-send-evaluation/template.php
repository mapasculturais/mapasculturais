<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
    mc-modal
');
?>
<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import(" 
    mc-modal
");
?>

<!-- <mc-modal title="<?= i::__('Finalizar e Enviar Avaliação') ?>"> -->

<div class="finish-send-evaluation">

    <div>
        <mc-modal button-label="Finalizar e Enviar Avaliação" title="<?= i::__('Avaliação feita!') ?>">
            <template #default>
                <div class="finish-send-evaluation__text">
                    <span class="finish-send-evaluation__span"><?= i::__('Agora é necessário enviar essa avaliação para a pessoa gestora.') ?></span>
                    <span><?= i::__('Você pode enviar uma por uma ou todas de uma só vez.') ?></span>
                </div>
            </template>

            <template #actions="modal">
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('Enviar Depois') ?></button>
                <button class="button button--primary" @click="modal.close()"><?= i::__('Enviar agora') ?></button>
            </template>

            <template #button="modal">
                <!-- @click="send(registration)" -->
                <button class="button button--primary button--icon button--large" @click="modal.open()">
                    <?= i::__('Enviar avaliação') ?>
                    <mc-icon name="upload"></mc-icon>
                </button>
            </template>
        </mc-modal>
    </div>
</div>