<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    oc-dialog
 ');

?>

<div class="settings-recaptcha">
    <oc-dialog>
        <template #content>
            <?= i::__('Configure aqui as credenciais do Google reCAPTCHA para proteger seu site contra acessos automáticos.') ?>
            <?= i::__('Solicite ao responsável de TI a chave do site e a chave secreta fornecidas pelo Google.') ?>
        </template>
    </oc-dialog>
    <div class="grid-12">
        <entity-field :entity="entity" prop="recaptcha_sitekey" classes="col-6"></entity-field>
        <entity-field :entity="entity" prop="recaptcha_secret" classes="col-6"></entity-field>
    </div>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>