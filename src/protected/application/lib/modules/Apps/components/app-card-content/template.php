<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div class="cardKey">
    <div class="cardKey__public">
        <label class="cardKey__label">Chave Pública:</label>
        <div class="cardKey__content--verified">
            <div class="cardKey__content">
                <span>{{entity.publicKey}}</span>
                <a class="cardKey__content--icon">
                    <mc-icon name="copy"></mc-icon>
                </a>
            </div>
        </div>
    </div>
    <div class="cardKey__private">

        <label class="cardKey__label">Chave Privada:</label>
        <div class="cardKey__content">
            <span>{{privateKey}}</span>

            <a class="cardKey__content--icon" @click="toggleKey()">
                <mc-icon name="eye-view"></mc-icon>
            </a>

            <a class="cardKey__content--icon">
                <mc-icon name="copy"></mc-icon>
            </a>
        </div>
    </div>
</div>