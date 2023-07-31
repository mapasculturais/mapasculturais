<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="cardKey">
    <div class="cardKey__public">
        <div class="cardKey__public--header">
            <div class="label"><?= i::__('Chave Pública:') ?></div>
            <a class="copy" @click="copyPublicKey()">
                <mc-icon name="copy"></mc-icon>
            </a>

        </div>
        <div class="cardKey__public--content">
            <span>{{entity.publicKey}}</span>
        </div>
    </div>

    <div class="cardKey__private">
        <div class="cardKey__private--header">
            <div class="label"><?= i::__('Chave Privada:') ?></div>
            <a class="view" @click="toggleKey()">
                <mc-icon name="eye-view"></mc-icon>
            </a>

            <a class="copy" @click="copyPrivateKey()">
                <mc-icon name="copy"></mc-icon>
            </a>
        </div>
        <div class="cardKey__private--content">
            <span>{{privateKey}}</span>
        </div>
    </div>
</div>