<?php 
use MapasCulturais\i;
$this->import('entity-field');
?>

<div class="entity-social-media">

    <h4 v-if="!editable" class="entity-social-media__title"> <?php i::_e("Redes sociais") ?> </h4>

    <div v-if="!editable" class="entity-social-media__links">

        <div v-if="entity.instagram" class="entity-social-media__links--link">
            <iconify icon="fa6-brands:instagram"></iconify>
            {{entity.instagram}}
        </div>

        <div v-if="entity.twitter" class="entity-social-media__links--link">
            <iconify icon="fa6-brands:twitter"></iconify>
            {{entity.twitter}}
        </div>

        <div v-if="entity.facebook" class="entity-social-media__links--link">
            <iconify icon="brandico:facebook-rect"></iconify>
            {{entity.facebook}}
        </div>

        <div v-if="entity.youtube" class="entity-social-media__links--link">
            <iconify icon="akar-icons:youtube-fill"></iconify>
            {{entity.youtube}}
        </div>

        <div v-if="entity.linkedin" class="entity-social-media__links--link">
            <iconify icon="akar-icons:linkedin-box-fill"></iconify>
            {{entity.linkedin}}
        </div>

        <div v-if="entity.spotify" class="entity-social-media__links--link">
            <iconify icon="akar-icons:spotify-fill"></iconify>
            {{entity.spotify}}
        </div>

        <div v-if="entity.pinterest" class="entity-social-media__links--link">
            <iconify icon="akar-icons:pinterest-fill"></iconify>
            {{entity.pinterest}}
        </div>
    </div>


    <h4 v-if="editable" class="entity-social-media__title"> <?php i::_e("Adicionar redes sociais") ?> </h4>

    <div v-if="editable" class="entity-social-media__edit">

        <div class="entity-social-media__edit--link">
            <iconify icon="fa6-brands:instagram"></iconify>
            <entity-field :entity="entity" prop="instagram"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="fa6-brands:twitter"></iconify>
            <entity-field :entity="entity" prop="twitter"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="brandico:facebook-rect"></iconify>
            <entity-field :entity="entity" prop="facebook"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:youtube-fill"></iconify>
            <entity-field :entity="entity" prop="youtube"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:linkedin-box-fill"></iconify>
            <entity-field :entity="entity" prop="linkedin"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:spotify-fill"></iconify>
            <entity-field :entity="entity" prop="spotify"></entity-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:pinterest-fill"></iconify>
            <entity-field :entity="entity" prop="pinterest"></entity-field>
        </div>
    </div>

</div>