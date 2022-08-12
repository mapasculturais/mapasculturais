<?php
use MapasCulturais\i;
?>
<div class="theme-logo" :style="{ '--logo-color': color }">
    <div class="theme-logo__logo">
        <div class="theme-logo__logo--part1"></div>
        <div class="theme-logo__logo--part2"></div>
    </div>
    
    <div class="theme-logo__text">
        <label class="theme-logo__text--title">{{title}}</label>
        <small class="theme-logo__text--subtitle" v-if="subtitle">{{subtitle}}</small>
    </div>
</div>