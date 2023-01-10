<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
');
?>

<div :class="classes">
    <div class="seal-relation-view__backlink">
        <mc-icon name="arrow-left"></mc-icon><a href="#">Voltar</a>
    </div>
    <div class="seal-relation-view__content">
        <div class="seal-relation-view__content--top">
            <img src="#" />
        </div>
        <div class="seal-relation-view__content--bottom">
            <h3>Selo de credentciamento para a 24 Feira Pan Amazônica do Livro</h3>
            <p>
                Certificamos que <b>PATRICK NOBRE</b> na condição de <b>AGENTE</b> recebeu o selo <b>SELO DE CREDENCIAMENTO PARA A 24 FEIRA PAN AMAZONICA DO LIVRO</b> no dia <b>13/12/2022</b>
                referente a sua participação em [SealShortDescripion]. Esta certificação tem validade até o dia 12/03/2023. Agradecemos sua participação.
                Atenciosamente, <b>SECRETARIA DE CULTURA DE MUNICIPIO</b>
            </p>
        </div>
        <div class="seal-relation-view__footer">
            Logo Mapas Culturais
        </div>
    </div>
    <div class="seal-relation-view__actions">
        <button class="button button--primary">
          <?= i::__('Compartilhar') ?>
        </button>
        <button class="button button--primary">
          <?= i::__('Imprimir') ?>
        </button>
    </div>
</div>