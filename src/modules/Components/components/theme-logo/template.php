<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<a class="theme-logo" :class="{'onlyImg': hideLabel}" :style="{'--logo-bg1': first_color, '--logo-bg2': second_color, '--logo-bg3': third_color, '--logo-bg4': fourth_color}" :href="href">    
    <div v-if="!logoImg" class="theme-logo__logo">
        <div class="part1"></div>
        <div class="part2"></div>
        <div class="part3"></div>
        <div class="part4"></div>
    </div>

    <div v-if="logoImg" class="theme-logo__logo--img">
        <img :src="logoImg">
    </div>
    
    <div v-if="!hideLabel" class="theme-logo__text">
        <span class="theme-logo__text--title">{{logo_title}}</span>
        <small class="theme-logo__text--subtitle" v-if="logo_subtitle">{{logo_subtitle}}</small>
    </div>
</a>