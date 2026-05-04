<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    theme-logo
    entity-field
    mc-image-uploader
');

?>

<fieldset class="logo-customizer">
    <legend class="logo-customizer__legend">
        <h3> <?= i::__('Customização da logo') ?> </h3>
    </legend>

    <div class="logo-customizer__content">        
        <div class="logo-customizer__settings grid-12">
            <entity-field :classes="'col-12'" :entity="subsite" prop="logo_use_image" :autosave="300"></entity-field>
            <entity-field :classes="'col-12'" :entity="subsite" prop="logo_hide_label" :autosave="300"></entity-field>

            <div v-if="subsite.logo_use_image === 'image'" class="logo-customizer__image col-12">
                <h4 class="logo-customizer__image-title"> <?= i::__('Imagem da logo') ?> </h4>
                <p class="logo-customizer__image-desc"> <?= i::__('Aqui você pode definir uma imagem para substituir o logo padrão do Mapas Culturais.') ?> </p>
                <mc-image-uploader class="logo-customizer__uploader" :entity="subsite" group="logo" :aspect-ratio="382/143" deleteFile>
                    <template #default="modal">
                        <div class="logo-customizer__uploader-content">
                            <img v-if="subsite.files.logo" :src="subsite.files.logo?.url" class="select-profileImg__img--img" alt="" />
                            <mc-icon v-if="!subsite.files.logo" name="image"></mc-icon>
                        </div>
                    </template>
                </mc-image-uploader>
            </div>

            <template v-if="subsite.logo_use_image !== 'image'">
                <entity-field :classes="'col-12'" :entity="subsite" prop="logo_title" :autosave="300"></entity-field>
                <entity-field :classes="'col-12'" :entity="subsite" prop="logo_subtitle" :autosave="300"></entity-field>
                <entity-field :classes="'col-12'" :entity="subsite" prop="custom_colors" :autosave="300"></entity-field>
                
                <div v-if="subsite.custom_colors" class="logo-customizer__color-inputs col-12">
                    <entity-field :entity="subsite" prop="logo_color1" :autosave="300"></entity-field>
                    <entity-field :entity="subsite" prop="logo_color2" :autosave="300"></entity-field>
                    <entity-field :entity="subsite" prop="logo_color3" :autosave="300"></entity-field>
                    <entity-field :entity="subsite" prop="logo_color4" :autosave="300"></entity-field>
                </div>
            </template>
        </div>

        <div class="logo-customizer__preview">
            <theme-logo 
                :bg1="colors.first" 
                :bg2="colors.second" 
                :bg3="colors.third" 
                :bg4="colors.fourth" 
                :title="title" 
                :subtitle="subtitle"
                :logo-img="logoImg"
                :hide-label="hideLabel"
            ></theme-logo>
        </div>
    </div>
</fieldset>