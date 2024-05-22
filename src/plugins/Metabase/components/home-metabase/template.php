<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-link
    mc-icon
');

?>
<div class="home-metabase">
    <div class="home-metabase__header">
        <div class="home-metabase__left">
            <h1 class="bold"><?php i::_e('Relatórios') ?></h1>
            <p class="semibold home-metabase__text"><?php i::_e('Acesse painéis de dados ao clicar no botão abaixo para visualizar gráficos e outras infomações importantes para consulta e análise') ?></p>
            <a :href="panelURl" :class="['button', 'button--primary-noborder', {'button--large': $media('max-width: 500px')}]"><?php i::_e('Confira todos os painéis') ?></a>

        </div>
        <div class="home-metabase__right">
            <img src="<?php $this->asset('img/business.png') ?>" class="home-metabase__img" />
        </div>
    </div>
    <div class="home-metabase__panel">
        <h4 v-if="!$media('max-width: 500px')" class="semibold home-metabase__subtitle"><?php i::_e('Confira abaixo alguns dados destacados') ?></h4>
        <p v-if="$media('max-width: 500px')" class="semibold home-metabase__subtitle"><?php i::_e('Confira abaixo alguns dados destacados') ?></p>
        <div class="home-metabase__content">
            <div v-for="card in cards" class="metabase-card" :class="{'metabase-card--double' : card.data.length > 1}">
                <div class="metabase-card__header">
                    <mc-icon class="metabase-card__icon" :class="card.iconClass" :name="card.icon"></mc-icon>
                    <h4 class="bold">{{card.label}}</h4>
                </div>
                <div class="metabase-card__content">
                    <div v-for="data in card.data" class="metabase-card__info">
                        <strong class="metabase-card__number" :class="lengthClass(data.value)">{{data.value}}</strong>
                        <label class="metabase-card__label">{{data.label}}</label>
                    </div>
                </div>
                <a :href="getUrl(card)" class="button button--primary button--large"><?php i::_e('Conferir painel de dados') ?></a>
            </div>

        </div>
    </div>

</div>