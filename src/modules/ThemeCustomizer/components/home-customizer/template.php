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

<div class="home-customizer">
    <h3> <?= i::__('Textos e imagens da Home') ?> </h3>

    <fieldset class="home-customizer__section" v-for="(section, index) in homeConfigurations" :key="index">
        <legend class="home-customizer__section-legend">
            <h4 class="bold">{{ section.sectionName }}</h4>
        </legend>

        <div :class="['home-customizer__group', {'home-customizer__group--hasImg' : section.image}]" ref="homeTextsContent">
            <div class="home-customizer__texts">
                <div class="field" v-for="(text, textIndex) in section.texts" :key="textIndex">
                    <label :for="text.slug">{{ text.description }}</label>
                    <textarea :id="text.slug" v-model="subsite.homeTexts[text.slug]" @change="subsite.save()"></textarea>
                </div>
            </div>
            
            <div class="home-customizer__image" v-if="section.image">
                <p class="home-customizer__image-title"> {{section.image.description}} </p>
                
                <mc-image-uploader class="home-customizer__uploader" :entity="subsite" :group="section.image.group" :aspect-ratio="section.image.aspectRatio" deleteFile>
                    <template #default="modal">
                        <div class="home-customizer__uploader-wrapper">
                            <div class="home-customizer__uploader-content">
                                <img v-if="subsite.files[section.image.group]" :src="subsite.files[section.image.group]?.url" class="select-profileImg__img--img" />
                                <mc-icon v-if="!subsite.files[section.image.group]" name="image"></mc-icon>
                            </div>
                        </div>
                    </template>
                </mc-image-uploader>
            </div>
        </div>
    </fieldset>
</div>