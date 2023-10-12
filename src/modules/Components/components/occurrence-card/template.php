<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon 
    mc-link
    mc-title
');
?>
<div class="entity-card occurrence-card">
    <div class="entity-card__header occurrence-card__card">
        <div class="entity-card__header occurrence-card__header">
            <mc-avatar :entity="event" size="medium"></mc-avatar>
            <div class="user-info">
                <mc-title  tag="h2" class="bold">
                    {{event.name}}
                </mc-title>
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
                <mc-link :entity="space">
                    <span class="space-adress__name space__color">{{space.name}}</span>
                </mc-link>
                <span class="space-adress__adress" v-if="space.endereco">- {{space.endereco}}</span>

            </div>
        </div>
        <div class="entity-card__content--occurrence-info">
            <div class="ageRating">
                <span class="ageRating__class uppercase"><?= i::__('Classificação') ?><strong>: </strong></span>

                <span class="ageRating__value uppercase">{{event.classificacaoEtaria}}</span>
            </div>
            <div v-if="occurrence.price" class="price ageRating">
                <span class="ageRating__class"><?= i::__('Entrada') ?><strong>: </strong></span>

                <span class="ageRating__value">{{occurrence.price}}</span>
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