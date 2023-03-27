<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('mc-link');
?>
<nav class="panel-nav" :entity="entity" :class="classes">
    <slot name='begin'></slot>
    <template v-for="group in groups" :key="group.id">
        <h3 v-if="group.label">{{group.label}}</h3>
        <ul v-if="group.items.length > 0">
            <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                <mc-link :route="item.route" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
            </li>
        </ul>
    </template>
    <slot name='end'>
        <li><mc-link :entity='entity' icon><?= i::__('Meu Perfil') ?></mc-link></li> 
       <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li> 
    </slot>
</nav>