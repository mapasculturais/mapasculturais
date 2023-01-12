<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    content-share
');
?>

<div :class="classes">
    <div class="seal-relation-view__main">
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
                <a class="theme-logo" href="#" title="mapa cultural" subtitle="do Pará" style="--logo-color:var(--mc-primary-500);">
                    <div class="theme-logo__logo">
                        <div class="theme-logo__logo--part1"></div>
                        <div class="theme-logo__logo--part2"></div>
                        <div class="theme-logo__logo--part1"></div>
                        <div class="theme-logo__logo--part2"></div>
                    </div>
                    <div class="theme-logo__text"><span class="theme-logo__text--title">Mapas</span><small class="theme-logo__text--subtitle">Culturais</small></div>
                </a>
            </div>
        </div>
        <div class="seal-relation-view__actions">
            <button class="button button--primary">
                <content-share></content-share>
            </button>
            <button class="button button--primary">
              <?= i::__('Imprimir') ?>
            </button>
        </div>
    </div>
</div>