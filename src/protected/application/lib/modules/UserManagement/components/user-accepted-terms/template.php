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
                <div v-for="(term, slug) in terms">
                    <div v-for="(value, key) in user" class="boxterm__term">
                        <div v-if="key.includes('lgpd')">
                            <label v-if="term.title== key">{{term.title}} </label>
                            <div class="boxterm__term"></div>
                            <div v-for="item in value">
                                <div v-if="item.md5 == term.md5">
                                O {{item.title}}termo em {{formatDate(item.timestamp)}} pelo ip {{item.ip}}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>