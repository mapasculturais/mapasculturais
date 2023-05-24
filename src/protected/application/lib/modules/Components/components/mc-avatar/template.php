<?php
use MapasCulturais\i;
$this->import('mc-icon');
?>
<div class="avatar" :class="classes"> 
    <img v-if="image" :src="image" alt="">
    <mc-icon v-if="!image" :entity="entity"></mc-icon>
</div>