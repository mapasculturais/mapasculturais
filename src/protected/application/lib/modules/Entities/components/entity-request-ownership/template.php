<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
');
?>
<div class="entity-request-ownership">
    <mc-confirm-button>
        <template #button="modal">
            <button @click="modal.open()" class="entity-request-ownership__button button button--primary button--md"><?= i::_e('Reivindicar Propriedade') ?></button>
        </template> 
        <template #message="message">
            <?php i::_e('Deseja reivindicar essa propriedade?') ?>
        </template> 
    </mc-confirm-button>
</div>