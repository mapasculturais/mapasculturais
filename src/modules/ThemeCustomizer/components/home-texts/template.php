<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<fieldset class="home-texts">
    <legend class="home-texts__legend">
        <h3> <?= i::__('Textos da Home') ?> </h3>
    </legend>

    <div class="home-texts__wrapper" v-for="(section, index) in homeTexts" :key="index">
        <h4 class="bold home-texts__title">{{ section.sectionName }}</h4>
        <div class="home-texts__content" ref="homeTextsContent">
            <div class="field" v-for="(text, textIndex) in section.texts" :key="textIndex">
                <label :for="text.slug">{{ text.description }}</label>
                <textarea :id="text.slug" v-model="subsite.homeTexts[text.slug]" @change="subsite.save()"></textarea>
            </div>
        </div>
    </div>
</fieldset>