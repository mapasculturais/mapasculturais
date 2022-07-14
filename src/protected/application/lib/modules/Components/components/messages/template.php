<?php
use MapasCulturais\i;
$this->import('mapas-container');
?>
<mapas-container class="messages">
    <template  v-for="message in messages">
        <div class="messages__message" :class="message.type">
            <div class="messages__message--snackbar"></div>
            <a @click="message.active=false"></a>
            <div class="messages__message--text">
                {{message.text}}
            </div>
        </div>
        
        </div>
    </template>
</mapas-container>

