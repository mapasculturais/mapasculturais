<?php 
use MapasCulturais\i;
$this->import('mapas-field');
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
            <mapas-field :entity="entity" prop="instagram"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="fa6-brands:twitter"></iconify>
            <mapas-field :entity="entity" prop="twitter"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="brandico:facebook-rect"></iconify>
            <mapas-field :entity="entity" prop="facebook"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:youtube-fill"></iconify>
            <mapas-field :entity="entity" prop="youtube"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:linkedin-box-fill"></iconify>
            <mapas-field :entity="entity" prop="linkedin"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:spotify-fill"></iconify>
            <mapas-field :entity="entity" prop="spotify"></mapas-field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:pinterest-fill"></iconify>
            <mapas-field :entity="entity" prop="pinterest"></mapas-field>
        </div>
    </div>

</div>