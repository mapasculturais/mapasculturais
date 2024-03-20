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
    <div class="grid-12">
        <?php i::_e("Bem-vindo:") ?>
    
        <mc-image-uploader :entity="subsite" group="welcomeBanner" :aspect-ratio="1500/600">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.welcomeBanner" :src="subsite.files.welcomeBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Oportunidade:") ?>
    
        <mc-image-uploader :entity="subsite" group="opportunityBanner" :aspect-ratio="800/306">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.opportunityBanner" :src="subsite.files.opportunityBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Evento:") ?>
    
        <mc-image-uploader :entity="subsite" group="eventBanner" :aspect-ratio="800/306">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.eventBanner" :src="subsite.files.eventBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Espaço:") ?>
    
        <mc-image-uploader :entity="subsite" group="spaceBanner" :aspect-ratio="800/306">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.spaceBanner" :src="subsite.files.spaceBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Agente:") ?>
    
        <mc-image-uploader :entity="subsite" group="agentBanner" :aspect-ratio="800/306">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.agentBanner" :src="subsite.files.agentBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Projeto:") ?>
    
        <mc-image-uploader :entity="subsite" group="projectBanner" :aspect-ratio="800/306">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.projectBanner" :src="subsite.files.projectBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>

    <div class="grid-12">
        <?php i::_e("Faça seu cadastro:") ?>
    
        <mc-image-uploader :entity="subsite" group="signupBanner" :aspect-ratio="1920/386">
            <template #default="modal">
                <div class="entity-profile__profile">
                    <div>
                        <mc-icon name="image"></mc-icon>
                        <img v-if="subsite.files.signupBanner" :src="subsite.files.signupBanner?.url" class="select-profileImg__img--img" />
                    </div>
                    <label class="entity-profile__profile--label">
                        <?php i::_e("Adicionar imagem de perfil"); ?>
                    </label>
                </div>
            </template>
        </mc-image-uploader>
    </div>
</fieldset>