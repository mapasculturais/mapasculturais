<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-image-uploader
');
?>

<fieldset class="image-customizer">
    <legend class="image-customizer__legend">
        <h3> <?= i::__('Customização de imagens') ?> </h3>
    </legend>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Oportunidade:") ?></label>
        </div>
    
        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="opportunityBanner" :aspect-ratio="800/306" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.opportunityBanner" :src="subsite.files.opportunityBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.opportunityBanner" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo da oportunidade"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Evento:") ?></label>
        </div>
    
        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="eventBanner" :aspect-ratio="800/306" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.eventBanner" :src="subsite.files.eventBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.eventBanner" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo do evento"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Espaço:") ?></label>
        </div>
    
        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="spaceBanner" :aspect-ratio="800/306" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.spaceBanner" :src="subsite.files.spaceBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.spaceBanner" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo do espaço"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Agente:") ?></label>
        </div>

        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="agentBanner" :aspect-ratio="800/306" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.agentBanner" :src="subsite.files.agentBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.agentBanner" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo do agente"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Projeto:") ?></label>
        </div>
    
        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="projectBanner" :aspect-ratio="800/306" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.projectBanner" :src="subsite.files.projectBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.projectBanner" name="image" v-if="!subsite.files.projectBanner"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo do projeto"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Faça seu cadastro:") ?></label>
        </div>
    
        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="signupBanner" :aspect-ratio="1920/386" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.signupBanner" :src="subsite.files.signupBanner?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.signupBanner" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem de fundo da seção de fazer cadastro"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="image-customizer__wrapper">
        <div class="field">
            <label><?php i::_e("Header") ?></label>
        </div>

        <mc-image-uploader class="image-customizer__uploader" :entity="subsite" group="header" :aspect-ratio="1920/386" deleteFile>
            <template #default="modal">
                <div class="uploader-wrapper">
                    <div class="uploader-content">
                        <img v-if="subsite.files.header" :src="subsite.files.header?.url" class="select-profileImg__img--img" />
                        <mc-icon v-if="!subsite.files.header" name="image"></mc-icon>
                    </div>
                    <label><?php i::_e("Adicionar imagem do header da home"); ?></label>
                </div>
            </template>
        </mc-image-uploader>
    </div>
</fieldset>