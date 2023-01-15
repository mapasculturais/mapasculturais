<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    seal-content-share
    theme-logo
');
?>

<div :class="classes">
    <div class="seal-relation-view__main">
        <div id="print">
            <div class="seal-relation-view__backlink">
                <mc-icon name="arrow-left" class="seal-relation-view__backlink--icon"></mc-icon><a href="#">Voltar</a>
            </div>
            <div class="seal-relation-view__content">
                <div class="seal-relation-view__content--top">
                    <div class="seal-relation-view__content--image">
                        <img :src="seal.avatar.avatarMedium.url" />
                    </div>
                </div>
                <div class="seal-relation-view__content--bottom">
                    <h3>{{ seal.name }}</h3>
                    <p>
                        Certificamos que <b>{{ agent.name }}</b> na condição de <b>AGENTE</b> recebeu o selo <b>{{ seal.name }}</b> no dia <b>{{ dateCreated }}</b>
                        referente a sua participação em {{ seal.shortDescription }}. Esta certificação tem validade até o dia {{ dateValidFormatted }}. Agradecemos sua participação.
                        Atenciosamente, <b>SECRETARIA DE CULTURA DE MUNICIPIO</b>
                    </p>
                </div>
                <div class="seal-relation-view__footer">
                    <theme-logo title="mapa cultural" subtitle="do Pará" color="#000000"></theme-logo>
                </div>
            </div>
        </div>
        <div class="seal-relation-view__actions">
            <button class="button button--primary">
                <seal-content-share></seal-content-share>
            </button>
            <button class="button button--primary" @click="print">
              <?= i::__('Imprimir') ?>
            </button>
        </div>
    </div>
</div>