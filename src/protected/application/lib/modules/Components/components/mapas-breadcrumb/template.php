<?php
use MapasCulturais\i;
?>

<nav class="mapas-breadcrumb" aria-label="<?= i::__('Breadcrumbs') ?>">
    <slot name="breadcrumbs">
        <ul>
            <li v-for="item in list">
                <a :href="item.url">
                    {{item.label}}
                </a>    
            </li>
        </ul>
    </slot>
</nav>