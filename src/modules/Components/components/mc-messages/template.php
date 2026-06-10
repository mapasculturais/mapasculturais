<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="messages">
    <div class="messages__content">
        <template  v-for="message in messages">
            <div class="messages__content__message" :class="message.type">
                <div class="messages__content__message-text" v-html="message.text"></div>
                <a class="messages__content__message-close" @click="message.active=false">
                    <mc-icon name="close"></mc-icon>
                </a>
            </div>
        </template>
    </div>
</div>

