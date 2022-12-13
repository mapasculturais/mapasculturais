<?php

use MapasCulturais\i;

// $this->import('

// ');
?>
<!-- 1- Verificar se o usuario tem os termos
     2- Verificar se a hash do termo atualizado existe -->
<!-- 3 - Verificar Timestamp, ip. -->
<div class="user-accepted-terms__privacy">
    <div class="user-accepted-terms__privacy-title">
        <label class="user-accepted-terms__privacy-title"><?= i::__('Aceite de termos') ?></label>
        <div v-if="user" class="verified">

            <div v-for="(term, slug) in terms" class="for">
                <!-- {{term.md5}} -->
                
                
                <!-- {{userslug}} -->
                <div v-if="term.md5 == privacyPolicy.acceptedmd5" class="verifica">
                    
                {{privacyPolicy.slug}} aceito em {{privacyPolicy}}
                {{privacyPolicy.acceptedmd5}}
                    
                    
                </div>
                <div v-if="term.md5 == termsOfUsage.acceptedmd5" class="verifica">
                    {{termsOfUsage.acceptedmd5}}
                    qualquer coisa
                    
                </div>
                <div v-if="term.md5 == termsUse.acceptedmd5" class="verifica">
                    {{termsUse.acceptedmd5}}
                    qualquer coisa
                    
                </div>
            </div>
        </div>
    </div>
    <div class="user-accepted-terms__privacy-accept">
        <label class="user-accepted-terms__privacy--accept-check">Termo aceito em pelo IP </label>
    </div>
</div>