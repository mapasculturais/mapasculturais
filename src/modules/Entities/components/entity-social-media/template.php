<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    entity-field
');
?>
<div :class="classes" v-if="editable || show" class="entity-social-media">

    <h4 v-if="!editable" class="entity-social-media__title"> <?php i::_e("Redes sociais") ?> </h4>

    <div v-if="!editable" class="entity-social-media__links">

        <div v-if="entity.instagram" class="entity-social-media__links--link">
            <mc-icon name="instagram"></mc-icon>
            {{entity.instagram}}
        </div>

        <div v-if="entity.twitter" class="entity-social-media__links--link">
            <mc-icon name="twitter"></mc-icon>
            {{entity.twitter}}
        </div>

        <div v-if="entity.facebook" class="entity-social-media__links--link">
            <mc-icon name="facebook"></mc-icon>
            {{entity.facebook}}
        </div>

        <div v-if="entity.youtube" class="entity-social-media__links--link">
            <mc-icon name="youtube"></mc-icon>
            {{entity.youtube}}
        </div>

        <div v-if="entity.linkedin" class="entity-social-media__links--link">
            <mc-icon name="linkedin"></mc-icon>
            {{entity.linkedin}}
        </div>
        <div v-if="entity.vimeo" class="entity-social-media__links--link">
            <mc-icon name="vimeo"></mc-icon>
            {{entity.vimeo}}
        </div>
        <div v-if="entity.spotify" class="entity-social-media__links--link">
            <mc-icon name="spotify"></mc-icon>
            {{entity.spotify}}
        </div>

        <div v-if="entity.pinterest" class="entity-social-media__links--link">
            <mc-icon name="pinterest"></mc-icon>
            {{entity.pinterest}}
        </div>
    </div>


    <h4 v-if="editable" class="entity-social-media__title"> <?php i::_e("Adicionar redes sociais") ?> </h4>

    <div v-if="editable" class="entity-social-media__edit">

        <div class="entity-social-media__edit--link">
            <mc-icon name="instagram"></mc-icon>
            <entity-field :entity="entity" prop="instagram"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <mc-icon name="twitter"></mc-icon>
            <entity-field :entity="entity" prop="twitter"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <mc-icon name="facebook"></mc-icon>
            <entity-field :entity="entity" prop="facebook"></entity-field>
        </div>
        <div class="entity-social-media__edit--link">
            <mc-icon name="vimeo"></mc-icon>
            <entity-field :entity="entity" prop="vimeo"></entity-field>
        </div>
        <div class="entity-social-media__edit--link">
            <mc-icon name="youtube"></mc-icon>
            <entity-field :entity="entity" prop="youtube"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <mc-icon name="linkedin"></mc-icon>
            <entity-field :entity="entity" prop="linkedin"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <mc-icon name="spotify"></mc-icon>
            <entity-field :entity="entity" prop="spotify"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <mc-icon name="pinterest"></mc-icon>
            <entity-field :entity="entity" prop="pinterest"></entity-field>
        </div>
    </div>

</div>