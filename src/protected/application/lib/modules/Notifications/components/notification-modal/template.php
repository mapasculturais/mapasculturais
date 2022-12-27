<?php
use MapasCulturais\i;
$this->import('
    mc-link
    modal 
    notification-list
');
?>
<modal v-if="notificationsCount > 0 && $media(mediaQuery)" :title="modalTitle" classes="create-modal" button-label="Notificações" @open="" @close="">
    <template #default>
        <div class="grid-12">
            <div class="col-6" v-if="notificationsCount > 0">
              <?= i::__('Você tem') ?>
                <span class="notification--badge">{{ notificationsCount }}</span>
              <?= i::__('notificação') ?>
            </div>
        </div>
        <div class="grid-12">
            <div class="col-12">
                <notification-list></notification-list>
            </div>
            <div class="col-12" v-if="notificationsCount > 0">
                <mc-link route="panel/notifications"><?= i::__('Ver todas as notificações') ?></mc-link>
            </div>
        </div>
    </template>
    <template #button="modal">
        <div class="grid-2" v-if="typeStyle == 'normal'">
            <div class="col-6">
                <a class="notification_header--link" @click="modal.open"><?= i::__('Notificações') ?></a>
            </div>
            <div class="col-6 notification_header--container">
                <a @click="modal.open">
                    <div>
                        <mc-icon width="18" name='notification'></mc-icon>
                    </div>
                    <div v-if="notificationsCount > 0" class="notification_header--badge">
                        <span>{{ notificationsCount }}</span>
                    </div>
                </a>
            </div>
        </div>
        <div class="grid-2" v-else-if="typeStyle == 'button'">
            <div class="col-6 notification_header--container">
                <a @click="modal.open">
                    <div>
                        <mc-icon width="18" name='notification'></mc-icon>
                    </div>
                    <div v-if="notificationsCount > 0" class="notification_header--badge">
                        <span>{{ notificationsCount }}</span>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a class="notification_header--link" @click="modal.open"><?= i::__('Notificações') ?></a>
            </div>
        </div>
    </template>
</modal>