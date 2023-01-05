<?php

use MapasCulturais\i;

$this->import('
    mc-link
    modal 
    notification-list
');
?>
<modal v-if="notificationsCount" :title="modalTitle" classes="create-modal" button-label="Notificações" @open="" @close="">
    <template #default>
        <div class="grid-12 notification">
            <div class="col-6 notification__header" v-if="notificationsCount">
                <span class="notification__header--text"><?= i::__('Você tem') ?></span>
                <span class="notification__header--badge">{{ notificationsCount }}</span>
                <span class="notification__header--text"><?= i::__('notificação') ?></span>
            </div>
        </div>
        <div class="grid-12">
            <div class="col-12">
                <notification-list styleCss='divider'></notification-list>
            </div>
            <div class="col-12 notification__content " v-if="notificationsCount">
                <mc-link route="panel/notifications"><?= i::__('Ver todas as notificações') ?></mc-link>
            </div>
        </div>
    </template>
    <template #button="modal">
        <div class="grid-2 notify-menu" v-if="viewport == 'desktop'">
            <div class="col-1">
                <a class="notification__header--link" @click="modal.open"><?= i::__('Notificações') ?></a>
            </div>
            <div class="col-1 notification__header--container">
                <a @click="modal.open">
                    <mc-icon name='notification'></mc-icon>
                    <div v-if="notificationsCount" class="notification__header--badge">
                        <span>{{ notificationsCount }}</span>
                    </div>
                </a>
            </div>
        </div>
        <div v-else-if="viewport == 'mobile'">
            <a href="#" @click="modal.open">
                <div class="notification__header--item-container">
                    <mc-icon class="alert" name='notification'></mc-icon>
                    <div v-if="notificationsCount" class="notification__header--item-badge">
                        <span>{{ notificationsCount }}</span>
                    </div>
                </div>
                <?= i::__('Notificações') ?>
            </a>
        </div>
    </template>
</modal>