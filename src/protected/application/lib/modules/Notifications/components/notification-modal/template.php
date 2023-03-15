<?php

use MapasCulturais\i;

$this->import('
    mc-link
    modal 
    notification-list
');
?>
<modal v-if="notificationsCount>0" :title="modalTitle" classes="create-modal notification-modal" button-label="<?= i::esc_attr__("Notificações") ?>" @open="" @close="">
    <template #default>
        <div class="grid-12 notification-modal__header">
            <div class="col-6 " v-if="notificationsCount>0">
                <label class="count">
                    <?= i::__('Você tem') ?>
                    <span class="count-counter">{{ notificationsCount }}</span>
                    <?= i::__('notificação') ?>
                </label>
            </div>
        </div>
        <div class="grid-12 notification-modal__content">
            <div class="col-12  notifications">
                <notification-list styleCss='divider'></notification-list>
            </div>
            <div class="col-12 link" v-if="notificationsCount">
                <mc-link route="panel/index" hash="notifications"><?= i::__('Ver todas as notificações') ?></mc-link>
            </div>
        </div>
    </template>
    <template #button="modal">
        <!-- v-if="viewport == 'desktop'" -->
        <div v-if="viewport=='desktop'" class="notification-modal__menu-desk" @click="modal.open">
            <label class="label" @click="modal.open"><?= i::__('Notificações') ?></label>
            <div class="icon">
                <mc-icon name='notification'></mc-icon>
                <span v-if="notificationsCount>0" class="count">{{notificationsCount}}</span>
            </div>
        </div>

        <!-- Refatorar -->
        <a v-if="viewport=='mobile'" class="notification-modal__menu-mobile" @click="modal.open">
            <div class="icon">
                <mc-icon name='notification'></mc-icon>
                <span v-if="notificationsCount>0" class="count">{{notificationsCount}}</span>
            </div>
            <label class="label" @click="modal.open"><?= i::__('Notificações') ?></label>
        </a>
           
    </template>
</modal>