<?php
use MapasCulturais\i;

$this->import('modal');

?>

<modal :title="modalTitle" classes="modal-classes" button-label="Texto do botão">
    
    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>

    <template #default>
    </template>

    <template #actions="modal">
    </template>

</modal>
