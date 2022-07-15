<?php
use MapasCulturais\i;
$this->import('mapas-container');
?>
<mapas-container class="messages">
    <template  v-for="message in messages">
        <div class="messages__message" :class="message.type">
            <div class="messages__message--text">
                {{message.text}}
            </div>
            <a class="messages__message--close" @click="message.active=false">
                <iconify icon="gg:close"></iconify>
            </a>
        </div>
    </template>
</mapas-container>

