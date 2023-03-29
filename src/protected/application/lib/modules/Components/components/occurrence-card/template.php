<?php

use MapasCulturais\i;

$this->import('mc-icon mc-link');
?>

<div class="entity-card">
    <div class="entity-card__header">
        <div class="entity-card__header user-details">
            <div class="user-image">
                <img v-if="event.files?.avatar" :src="event.files?.avatar?.transformations?.avatarMedium.url" />
                <mc-icon v-else name="event"></mc-icon>
            </div>
            <div class="user-info">
                <label class="user-info__name">
                    {{event.name}}
                </label>
                <div class="user-info__attr">
                    <span> {{event.subTitle}} </span>
                </div>
            </div>
        </div>
        <div class="entity-card__header user-slot">
            <slot name="labels"></slot>
        </div>
    </div>

    <div class="entity-card__content">
        <div class="entity-card__content--occurrence-data">
            <mc-icon name="event"></mc-icon> {{occurrence.starts.date('long')}} <?= i::_e('às') ?> {{occurrence.starts.time()}}
        </div>
        <div v-if="!hideSpace" class="entity-card__content--occurrence-space">
            <div class="link"><mc-icon class="link space__color" name="pin"></mc-icon></div>
            <div class="space-adress">
                <span class="space-adress__name space__color">{{space.name}}</span>
                <span class="space-adress__adress" v-if="space.endereco">- {{space.endereco}}</span>
            </div>
        </div>




        <div class="entity-card__content--occurrence-info">
            <div class="ageRating">
                <?= i::__('Classificação') ?>: <strong>{{event.classificacaoEtaria}}</strong>
            </div>
            <div v-if="occurrence.price" class="price">
                <?= i::__('Entrada') ?>: <strong>{{occurrence.price}}</strong>
            </div>
        </div>
        <div class="entity-card__content--terms">
            <div v-if="tags" class="entity-card__content--terms-tag">
                <label class="tag__title">
                    <?php i::_e('Tags:') ?> ({{event.terms.tag.length}}):
                </label>
                <p :class="['terms', 'event__color']"> {{tags}} </p>
            </div>
            <div v-if="linguagens" class="entity-card__content--terms-linguagem">
                <label class="linguagem__title">
                    <?php i::_e('linguagens:') ?> ({{event.terms.linguagem.length}}):
                </label>
                <p :class="['terms', 'event__color']"> {{linguagens}} </p>
            </div>
        </div>
    </div>
    <div class="entity-card__footer">
        <div class="entity-card__footer--info">
            <div v-if="seals" class="seals">
                <label class="seals__title">
                    <?php i::_e('Selos') ?> ({{event.seals.length}}):
                </label>
                <div v-for="seal in seals" class="seals__seal"></div>
                <div v-if="seals.length == 2" class="seals__seal more">+1</div>
            </div>
        </div>
        <div class="entity-card__footer--action">
            <a :href="event.singleUrl" class="button button--primary button--large button--icon">
                <?php i::_e('Acessar') ?>
                <mc-icon name="access"></mc-icon>
            </a>
        </div>
    </div>
</div>