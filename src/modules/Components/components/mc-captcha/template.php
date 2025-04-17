<?php

use MapasCulturais\i;

?>

<div style="display: flex; flex-direction: column; align-items: center;">
    <!-- Google Recaptcha -->
    <VueRecaptcha v-if="provider == 'google'" :sitekey="key" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha"></VueRecaptcha>


    <!-- Cloudflare Turnstile -->
    <div v-if="provider == 'cloudflare'" id="container-cloudflare-turnstile"></div>
</div>