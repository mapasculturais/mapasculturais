<?php
use MapasCulturais\i;
$this->import('mapas-container');
?>
<div class="messages">
    <div class="messages__content">
        <template  v-for="message in messages">
            <div class="messages__content--message" :class="message.type">
                <div class="messages__content--message-text">
                    {{message.text}}
                </div>
                <a class="messages__content--message-close" @click="message.active=false">
                    <iconify icon="gg:close"></iconify>
                </a>
            </div>
        </template>
    </div>
</div>

