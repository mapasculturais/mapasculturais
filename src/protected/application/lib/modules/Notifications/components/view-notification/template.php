<?php
    use MapasCulturais\i;
    $this->import('
    mc-link
    modal 
    notification-list
');
?>


<modal :title="modalTitle" classes="create-modal" button-label="Notificações" @open="" @close="">
    <template #default>
        <div class="grid-12">
            <div class="col-12">
                <h1>Notificações</h1>
            </div>
            <div class="col-6">
                Você tem 1 notificação
            </div>
            <div class="col-6" style="text-align: right">
                Marcar todas como lidas
            </div>
        </div>
        <div class="grid-12">
            <div class="col-12">
                <notification-list></notification-list>
            </div>
            <div class="col-12">
                <p style="text-align: center">Ver todas as notificações</p>
            </div>
        </div>
    </template>
</modal>