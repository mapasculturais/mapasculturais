<?php
use MapasCulturais\i;
?>

<div class="entity-request-ownership">
    <confirm-button>
        <template #button="modal">
            <button @click="modal.open()" class="entity-request-ownership__button button button--primary button--md"><?= i::_e('Reivindicar Propriedade') ?></button>
        </template> 
        <template #message="message">
            <?php i::_e('Deseja reivindicar essa propriedade?') ?>
        </template> 
    </confirm-button>
</div>