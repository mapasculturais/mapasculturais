<?php
use MapasCulturais\i;
$this->import('mapas-container');
?>
<mapas-container class="messages">
    <template  v-for="message in messages">
        <div class="messages__message" :class="message.type">
            <a @click="message.active=false"></a>
            <div class="messages__message--text">
                
            <div class="message.type">
            {{message.text}}
            </div>
        </div>
        
        </div>
    </template>
</mapas-container>

