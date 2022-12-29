<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div class="cardKey">
    <div class="cardKey__content">
        <div class="cardKey__content--pvt">
            <label class="cardKey__content--pvt-label">Chave Pública:</label>
            <div class="cardKey__content--pvt-icon">

                <a class="cardKey__content--pvt--icon-symbol" @click="copyPrivateKey()">
                    <mc-icon name="copy"></mc-icon>
                </a>
            </div>
        </div>
        <div class="cardKey__content--key">
            <span>{{entity.publicKey}}</span>
        </div>
    </div>
    <div class="cardKey__private">

        <div class="cardKey__content">
            <div class="cardKey__content--pvt">
                <label class="cardKey__content--pvt--label">Chave Privada:</label>
                <div class="cardKey__content--pvt-icon">

                    <a class="cardKey__content--pvt--icon-symbol" @click="toggleKey()">
                        <mc-icon name="eye-view"></mc-icon>
                    </a>

                    <a class="cardKey__content--pvt--icon-symbol" @click="copyPrivateKey()">
                        <mc-icon name="copy"></mc-icon>
                    </a>
                </div>
            </div>
            <div class="cardKey__content--key">
                <span>{{privateKey}}</span>
            </div>
        </div>
    </div>
</div>