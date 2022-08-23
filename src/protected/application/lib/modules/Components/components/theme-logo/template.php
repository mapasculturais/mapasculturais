<?php
use MapasCulturais\i;
?>
<div :style="{ '--logo-color': color }">
    <a class="theme-logo" :href="href">    
        <div class="theme-logo__logo">
            <div class="theme-logo__logo--part1"></div>
            <div class="theme-logo__logo--part2"></div>
        </div>
        
        <div class="theme-logo__text">
            <span class="theme-logo__text--title">{{title}}</span>
            <small class="theme-logo__text--subtitle" v-if="subtitle">{{subtitle}}</small>
        </div>
    </a>
</div>