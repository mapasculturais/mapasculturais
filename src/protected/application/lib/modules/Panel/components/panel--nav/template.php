<?php
use MapasCulturais\i;

$this->import('mc-link')
?>
<nav :class="classes">
    <slot name='begin'></slot>
    <template  v-for="group in groups" :key="group.id">
        <h3 v-if="group.label">{{group.label}}</h3>
        <ul v-if="group.items.length > 0">
            <li 
                v-for="item in group.items" 
                :key="`${group.id}:${item.route}`" 
                >
                <mc-link :route="item.route" :icon="item.icon" :class="{'active': item.route == route}">{{item.label}}</mc-link>
            </li>
        </ul>
    </template>
    <slot name='end'></slot>
</nav>

