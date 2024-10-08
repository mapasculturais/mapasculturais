<?php

use MapasCulturais\i;

?>

<div>
    <!-- Google Recaptcha -->
    <VueRecaptcha v-if="provider == 'google'" :sitekey="key" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha"></VueRecaptcha>
</div>