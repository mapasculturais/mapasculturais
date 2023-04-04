<?php
use MapasCulturais\i;
?>

<a class="theme-logo" :style="{'--logo-color1': primaryBackground, '--logo-color2': secondaryBackground}" :href="href">    
    <div class="theme-logo__logo">
        <div class="theme-logo__logo--part1"></div>
        <div class="theme-logo__logo--part2"></div>
        <div class="theme-logo__logo--part1"></div>
        <div class="theme-logo__logo--part2"></div>
    </div>
    
    <div class="theme-logo__text">
        <span class="theme-logo__text--title">{{title}}</span>
        <small class="theme-logo__text--subtitle" v-if="subtitle">{{subtitle}}</small>
    </div>
</a>