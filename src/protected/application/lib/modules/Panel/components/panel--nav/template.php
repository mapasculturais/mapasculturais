<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
');
?>
<nav v-if="viewport=='desktop'" class="panel-nav" :class="classes">
    <slot name='begin'></slot>
    <div class="panel-nav__left">
        <template v-for="group in leftGroups" :key="group.id">
            <h3 v-if="group.label">{{group.label}}</h3>
            <ul v-if="group.items.length > 0">
                <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                    <mc-link :route="item.route" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
                </li>
            </ul>
        </template>
        <template v-if="sidebar">
            <div class="panel-nav__line"></div>
            <div class="panel-nav__right">
                <li class="myaccount"><mc-link :entity='entity' icon><?= i::__('Meu Perfil') ?></mc-link></li>
                <li class="exit"><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
            </div>
        </template>
    </div>
    <div v-if="!sidebar" class="vertical__line"></div>
    <div v-if="!sidebar" class="panel-nav__right">
        <template v-for="group in rightGroups" :key="group.id">
            <h3 v-if="group.label">{{group.label}}</h3>
            <ul v-if="group.items.length > 0">
                <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                    <mc-link :route="item.route" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
                </li>
            </ul>
        </template>
        <div class="panel-nav__line"></div>
        <div class="panel-nav__right">
            <li class="myaccount"><mc-link :entity='entity' icon><?= i::__('Meu Perfil') ?></mc-link></li>
            <li class="exit"><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
        </div>
    </div>
</nav>

<nav v-if="viewport=='mobile'" class="panel-nav" :entity="entity" :class="classes">
    <template v-for="group in groupsColumn" :key="group.id">
        <h3 v-if="group.label">{{group.label}}</h3>
        <ul v-if="group.items.length > 0">
            <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                <mc-link :route="item.route" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
            </li>
        </ul>
    </template>
    <div class="panel-nav">
        <li><mc-link :entity='entity' icon><?= i::__('Meu Perfil') ?></mc-link></li>
        <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
    </div>
</nav>