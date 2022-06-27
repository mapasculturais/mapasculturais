<?php 
use MapasCulturais\i;
$this->import('field');
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
            <field :entity="entity" prop="instagram"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="fa6-brands:twitter"></iconify>
            <field :entity="entity" prop="twitter"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="brandico:facebook-rect"></iconify>
            <field :entity="entity" prop="facebook"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:youtube-fill"></iconify>
            <field :entity="entity" prop="youtube"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:linkedin-box-fill"></iconify>
            <field :entity="entity" prop="linkedin"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:spotify-fill"></iconify>
            <field :entity="entity" prop="spotify"></field>
        </div>

        <div class="entity-social-media__edit--link">
            <iconify icon="akar-icons:pinterest-fill"></iconify>
            <field :entity="entity" prop="pinterest"></field>
        </div>
    </div>

</div>