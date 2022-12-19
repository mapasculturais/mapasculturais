<?php

use MapasCulturais\i;
?>

<div class="user-accepted-terms__privacy">
    <div class="user-accepted-terms__privacy--accept">
        <label class="user-accepted-terms__privacy--accept-title"><?= i::__('Aceite de termos') ?></label>
        <div v-if="user" class="user-accepted-terms__privacy--accept-title-box">
            <div class="boxterm">
                <div v-for="(term, slug) in terms" class="boxterm__list">
                    <div v-if="user['lgpd_'+ slug]?.[term.md5]">
                        <label class="boxterm__list-subterm">
                            <label class="boxterm__list-subterm-title"><?= i::__('{{term.title}}')?></label>
                            <label class="boxterm__list-subterm-content"><?= i::__(' aceitos em {{formatDate(user["lgpd_"+slug][term.md5].timestamp)}} pelo ip {{user["lgpd_"+slug][term.md5].ip}}')?></label> 
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>