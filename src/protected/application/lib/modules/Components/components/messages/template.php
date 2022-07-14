<?php
use MapasCulturais\i;
$this->import('mapas-container');
?>
<mapas-container class="messages">
    <template  v-for="message in messages">
        <div class="messages__message" :class="message.type">
            <button class="button button-primary" @click="message.active=false">x</button>{{message.text}}
        </div>
    </template>
</mapas-container>