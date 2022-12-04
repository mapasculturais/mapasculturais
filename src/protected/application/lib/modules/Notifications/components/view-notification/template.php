<?php
    use MapasCulturais\i;
    $this->import('
    mc-link
    modal 
    notification-list
');
?>


<modal :title="modalTitle" classes="create-modal" button-label="Ver Notificacoes" @open="" @close="">
    <template #default>
        <notification-list></notification-list>
    </template>
</modal>