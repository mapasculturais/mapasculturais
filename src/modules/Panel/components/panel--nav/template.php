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
        <?php $this->applyTemplateHook("panel-nav-left-groups","before" )?>
        <template v-for="group in leftGroups" :key="group.id">
            <?php $this->applyTemplateHook("panel-nav-left-groups","begin" )?>
            <h3 v-if="group.label">{{group.label}}</h3>
            <ul v-if="group.items.length > 0">
                <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                    <mc-link :route="item.route" :params="item.params" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
                </li>
            </ul>
            <?php $this->applyTemplateHook("panel-nav-left-groups","end" )?>
        </template>
        <?php $this->applyTemplateHook("panel-nav-left-groups","after" )?>
        
        <?php $this->applyTemplateHook("panel-nav-left-sidebar","before" )?>
        <template v-if="sidebar">
            <?php $this->applyTemplateHook("panel-nav-left-sidebar","begin" )?>
            <div class="panel-nav__line"></div>
            <div class="panel-nav__right panel-nav__right--user">
                <li v-for="item in userGroup.items" :key="`user:${item.route}`">
                    <mc-link :route="item.route" :params="item.params" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
                </li>
            </div>
            <?php $this->applyTemplateHook("panel-nav-left-sidebar","end" )?>
        </template>
        <?php $this->applyTemplateHook("panel-nav-left-sidebar","after" )?>
    </div>
    <div v-if="!sidebar" class="vertical__line"></div>
    <div v-if="!sidebar" class="panel-nav__right">
        <template v-for="group in rightGroups" :key="group.id">
            <h3 v-if="group.label">{{group.label}}</h3>
            <ul v-if="group.items.length > 0">
                <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                    <mc-link :route="item.route" :params="item.params" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
                </li>
            </ul>
        </template>
        <div class="panel-nav__line"></div>
        <div class="panel-nav__right panel-nav__right--user">
            <li v-for="item in userGroup.items" :key="`user:${item.route}`">
                <mc-link :route="item.route" :params="item.params" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
            </li>
        </div>
    </div>
</nav>

<nav v-if="viewport=='mobile'" class="panel-nav" :entity="entity" :class="classes">
    <template v-for="group in groupsColumn" :key="group.id">
        <h3 v-if="group.label">{{group.label}}</h3>
        <ul v-if="group.items.length > 0">
            <li v-for="item in group.items" :key="`${group.id}:${item.route}`">
                <mc-link :route="item.route" :params="item.params" :icon="item.icon" :class="{'active': active(item)}">{{item.label}}</mc-link>
            </li>
        </ul>
    </template>
    <div class="panel-nav">
        <li><mc-link :entity='entity' icon><?= i::__('Meu Perfil') ?></mc-link></li>
        <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
    </div>
</nav>