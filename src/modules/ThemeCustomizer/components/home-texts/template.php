<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="home-texts">
    <div class="home-texts__wrapper" v-for="(section, index) in homeTexts" :key="index">
        <h3 class="home-texts__title">{{ section.sectionName }}</h3>
        <div class="home-texts__content" ref="homeTextsContent">
            <div class="field" v-for="(text, textIndex) in section.texts" :key="textIndex">
                <label :for="text.slug">{{ text.description }}</label>
                <textarea :id="text.slug" v-model="subsite.homeTexts[text.slug]" @change="subsite.save()"></textarea>
            </div>
        </div>
    </div>
</div>