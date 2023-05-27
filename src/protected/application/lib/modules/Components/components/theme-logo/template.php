<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<a class="theme-logo" :class="{'onlyImg': hideLabel}" :style="{'--logo-bg1': colors.bg1, '--logo-bg2': colors.bg2, '--logo-bg3': colors.bg3, '--logo-bg4': colors.bg4}" :href="href">    
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
        <span class="theme-logo__text--title">{{title}}</span>
        <small class="theme-logo__text--subtitle" v-if="subtitle">{{subtitle}}</small>
    </div>
</a>