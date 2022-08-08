<?php
use MapasCulturais\i;

$this->import('mc-icon');
?>

<div class="entity-card">
    
    <div class="entity-card__header">
        <div class="entity-card__header--image">
            <img v-if="entity.files?.avatar" :src="entity.files?.avatar?.transformations?.avatarMedium.url" />
            <mc-icon v-else :entity="entity"></mc-icon>
        </div>

        <div :class="['entity-card__header--type',  entity.__objectType+'__background']">
            <mc-icon :entity="entity"></mc-icon>
            {{entity.__objectType}}
        </div>
        
        <div class="entity-card__header--title">
            <label class="entity-card__header--title-title"> 
                {{entity.name}}
            </label>

            <div class="entity-card__header--title-metadata">
                <span v-if="entity.type"> <?php i::_e('Tipo:') ?> {{entity.type.name}} </span>
            </div>
        </div>
    </div>

    <div class="entity-card__content">
        <div class="entity-card__content--description">
            {{entity.shortDescription}}
        </div>

        <div class="entity-card__content--terms">
            <div v-if="areas" class="entity-card__content--terms-area">
                <label class="area__title">
                    <?php i::_e('Áreas de atuação:') ?> ({{entity.terms.area.length}}):
                </label>
                <p :class="['terms', entity.__objectType+'__color']"> {{areas}} </p>
            </div>
            
            <div v-if="tags" class="entity-card__content--terms-tag">
                <label class="tag__title">
                    <?php i::_e('Tags:') ?> ({{entity.terms.tag.length}}):
                </label>
                <p :class="['terms', entity.__objectType+'__color']"> {{tags}} </p>
            </div>

            <div v-if="linguagens" class="entity-card__content--terms-linguagem">
                <label class="linguagem__title">
                    <?php i::_e('linguagens:') ?> ({{entity.terms.linguagem.length}}):
                </label>
                <p :class="['terms', entity.__objectType+'__color']"> {{linguagens}} </p>
            </div>
        </div>
    </div>

    <div class="entity-card__footer">
        <div class="entity-card__footer--info">
            <div v-if="seals" class="seals">
                <label class="seals__title"> 
                    <?php i::_e('Selos') ?> ({{entity.seals.length}}):
                </label>
                <div v-for="seal in seals" class="seals__seal"></div>
                <div v-if="seals.length == 2" class="seals__seal more">+1</div>
            </div>
        </div>

        <div class="entity-card__footer--action">
            <button class="button button--primary button--large button--icon"> 
                <?php i::_e('Acessar') ?> 
                <mc-icon name="access"></mc-icon> 
            </button>
        </div>
    </div>

</div>