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
                    <div v-if="term.md5 == privacyPolicy.acceptedmd5" class="boxterm__list">

                        <label class="boxterm__list-subterm">{{privacyPolicy.slug}}</label> <label class="boxterm__list-term">aceito em {{privacyPolicy.timestamp}} pelo ip {{privacyPolicy.ip}}</label>

                    </div>
                    <div v-if="term.md5 == termsOfUsage.acceptedmd5" class="boxterm__list">
                        <label class="boxterm__list-subterm">{{termsOfUsage.slug}}</label> <label class="boxterm__list-term">aceito em {{termsOfUsage.timestamp}} pelo ip {{termsOfUsage.ip}}</label>


                    </div>
                    <div v-if="term.md5 == termsUse.acceptedmd5" class="boxterm__list">
                        <label class="boxterm__list-subterm">{{termsUse.slug}}</label> <label class="boxterm__list-term">aceito em {{termsUse.timestamp}} pelo ip {{termsUse.ip}}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>