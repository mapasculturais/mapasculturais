<?php

use MapasCulturais\i;

// $this->import('

// ');
?>
<!-- 1- Verificar se o usuario tem os termos
     2- Verificar se a hash do termo atualizado existe -->
<!-- 3 - Verificar Timestamp, ip. -->
<div class="user-accepted-terms__privacy">
    <div class="user-accepted-terms__privacy--accept">
        <label class="user-accepted-terms__privacy--accept-title"><?= i::__('Aceite de termos') ?></label>
        <div v-if="user" class="user-accepted-terms__privacy--accept-title-box">

            <div class="boxterm">
                <div v-for="(term, slug) in terms" class="boxterm__list">
                    <div v-for="(value, key) in user" class="boxterm__list-term">
                        <div v-if="key.includes('lgpd')">
                            <div v-for="item in value" class="boxterm__list-subterm">
                                <div v-if="item.md5 == term.md5">
                                <label><?= i::__('{{term.title}} aceitos em {{formatDate(item.timestamp)}} pelo ip {{item.ip}}')?></label> 
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>