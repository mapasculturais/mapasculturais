<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="messages">
    <div class="messages__content">
        <template v-for="message in messages">
            <div class="messages__content--message" :class="[message.type, {'messages__content--message-structured': message.messages?.length}]">
                <div class="messages__content--message-text">
                    <div v-if="message.messages?.length" role="alert" aria-live="assertive" aria-atomic="true">
                        <strong v-if="message.title" class="messages__content--message-title">{{ message.title }}</strong>
                        <ul class="messages__content--message-list">
                            <li v-for="(validationMessage, index) in message.messages" :key="index">{{ validationMessage }}</li>
                        </ul>
                    </div>
                    <span v-else v-html="message.text"></span>
                </div>
                <button type="button" class="messages__content--message-close" aria-label="<?= i::esc_attr__('Fechar mensagem') ?>" @click="store.dismiss(message)">
                    <mc-icon name="close"></mc-icon>
                </button>
            </div>
        </template>
    </div>
</div>
