<?php
use MapasCulturais\i;
?>

<div class="entity-card">
    
    <div class="entity-card__header">
        <div class="entity-card__header--image">
            <img v-if="entity.files?.avatar" :src="entity.files?.avatar?.transformations?.avatarMedium.url" />
            <iconify v-else icon="bi:image-fill" />
        </div>
        <div class="entity-card__header--title">
            <label class="entity-card__header--title-title"> 
                {{entity.name}}    
            </label>
            <div class="entity-card__header--title-metadata">
                <span> <?php i::_e('Tipo:') ?> Tipo do projeto </span>
            </div>
        </div>

    </div>

    <div class="entity-card__content">
        <div class="entity-card__content--description">
            Ac massa tempus mattis dictum. Eu molestie morbi a mattis pretium et lectus egestas euismod. Cras at quis tincidunt vel feugiat enim, felis ut amet. Nibh sit nulla eget purus quam porta non. Erat condimentum sapien amet suspendisse diam, nunc massa consectetur. Morbi sed ac massa elementum. Rhoncus viverra lorem interdum eu quis facilisis tempus. Auctor laoreet varius eu pretium congue. É isso aí.
        </div>
    </div>

    <div class="entity-card__footer">
        <div class="entity-card__footer--data">
            <div class="seals">
                <label class="seals__title"> <?php i::_e('Selos') ?> (3):</label>

                <div class="seals__seal">

                </div>
                <div class="seals__seal">
                    
                </div>
                <div class="seals__seal more">
                    +1
                </div>
            </div>
        </div>
        <div class="entity-card__footer--action">
            <button class="button button--primary button--large button--icon"> <?php i::_e('Acessar') ?> <iconify icon="ooui:previous-rtl"></iconify> </button>
        </div>
    </div>

</div>