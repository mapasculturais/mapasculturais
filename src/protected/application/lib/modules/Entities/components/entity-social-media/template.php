<?php 
use MapasCulturais\i;
$this->import('field');
?>

<div class="entity-social-media">

    <h4 v-if="!editable" class="entity-social-media__title"> <?php i::_e("Redes sociais") ?> </h4>

    <ul v-if="!editable" class="entity-social-media__links">
        
        <li v-if="entity.twitter">
            <a :href="entity.twitter" class="entity-social-media__links--link" aria-label="Twitter" target="_blank">
                <iconify icon="fa6-brands:twitter"></iconify> {{entity.twitter}}
            </a>
        </li>

        <li v-if="entity.facebook">
            <a :href="entity.facebook" class="entity-social-media__links--link" aria-label="Facebook" target="_blank">
                <iconify icon="la:facebook-f"></iconify> {{entity.facebook}}
            </a>
        </li>

        <li v-if="entity.instagram">
            <a :href="entity.instagram" class="entity-social-media__links--link" aria-label="Instagram" target="_blank">
                <iconify icon="fa6-brands:instagram"></iconify> {{entity.instagram}}
            </a>
        </li>

        <li v-if="entity.telegram">
            <a :href="entity.telegram" class="entity-social-media__links--link" aria-label="Telegram" target="_blank">
                <iconify icon="bxl:telegram"></iconify> {{entity.telegram}}
            </a>
        </li>

        <li v-if="entity.pinterest">
            <a :href="entity.pinterest" class="entity-social-media__links--link" aria-label="Pinterest" target="_blank">
                <iconify icon="fa6-brands:pinterest-p"></iconify> {{entity.pinterest}}
            </a>
        </li>

        <li v-if="entity.whatsapp">
            <a :href="entity.whatsapp" class="entity-social-media__links--link" aria-label="WhatsApp" target="_blank">
                <iconify icon="fa6-brands:whatsapp"></iconify> {{entity.whatsapp}}
            </a>
        </li>   

    </ul>

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