<?php

use MapasCulturais\i;

// $this->import('

// ');
?>

<div class="user-accepted-terms__privacy">
    <div class="user-accepted-terms__privacy-title">
        <label class="user-accepted-terms__privacy-title"><?= i::__('Aceite de termos') ?></label>
       <div v-if="user"class="verified">

        <div v-for="(term, slug) in terms" class="for">
            <label v-if="user.LGPD.lgpd_privacypolicy == term.md5">O usuario aceitou os termos em</label>
            {{user.LGPD.privacyPolicy}}
            {{term.title}}
            {{term.md5}}
        </div>
        </div>
    </div>
    <div class="user-accepted-terms__privacy-accept">
        <label class="user-accepted-terms__privacy--accept-check">Termo aceito em pelo IP </label>
    </div>
</div>