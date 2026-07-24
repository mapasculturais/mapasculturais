<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-avatar
    mc-icon
    mc-title
');
?>
<?php $this->applyTemplateHook('entity-header', 'before'); ?>
<header v-if="!editable" class="entity-header" :class="[{ 'entity-header--no-image': !entity.files.header }, 'entity-header--' + entity.__objectType]">
    <?php $this->applyTemplateHook('entity-header', 'begin'); ?>
    <div class="entity-header__single--cover" :style="{ '--url': url(entity.files.header?.url) }"></div>
    <div class="entity-header__single--content">
        <div class="leftSide">
            <div class="avatar">
                <mc-avatar :entity="entity" size="big"></mc-avatar>
            </div>
            <div class="data">
                <div class="entity-header__identity">
                    <mc-title tag="h1" size="big" class="entity-header__title"> {{entity.name}} </mc-title>
                    <span v-if="entity.type && entity.__objectType === 'agent'" :class="['entity-header__type-badge', entity.__objectType + '__background']">
                        <mc-icon :name="entity.type?.id == 1 ? 'agent-1' : 'agent-2'"></mc-icon>
                        {{entity.type.name}}
                    </span>
                    <?php $this->applyTemplateHook('entity-header-seals', 'before'); ?>
                    <slot name="seals"></slot>
                    <?php $this->applyTemplateHook('entity-header-seals', 'after'); ?>
                </div>
                <div class="metadata">
                    <slot name="metadata">
                        <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                            <dt class="metadata__id--id"><?= i::__('ID') ?></dt>
                            <dd><strong>{{entity.id}}</strong></dd>
                        </dl>
                        <dl v-if="entity.type && entity.__objectType !== 'agent'">
                            <dt><?= i::__('Tipo')?></dt>
                            <dd :class="[entity.__objectType+'__color', 'type']">{{entity.type.name}} </dd>
                        </dl>
                    </slot>
                </div>
            </div>
        </div>
        <div class="rightSide">
            <div class="entity-header__actions">
                <slot name="actions"></slot>
            </div>
            <div v-if="entity.site" class="site">
                <a :href="entity.site" target="_blank"><mc-icon :class="entity.__objectType+'__color'" name="link"></mc-icon>{{entity.site}}</a>
            </div>
        </div>
        <div v-if="entity.shortDescription" :class="['description', 'entity-header__short-description', {'description--event':entity.__objectType=='event'}]">
            <slot name="description">
                <p class="description" v-html="entity.shortDescription"></p>
            </slot>
        </div>
    </div>

    <?php $this->applyTemplateHook('entity-header', 'end'); ?>

</header>

<header v-if="editable" class="entity-header">
    <?php $this->applyTemplateHook('entity-header', 'begin'); ?>
    <div class="entity-header__edit">
        <div class="entity-header__edit--content">
            <div class="title">
                <div :class="['icon', entity.__objectType+'__background']">
                    <mc-icon :entity="entity"></mc-icon>
                </div>
                <h2 v-if="this.entity.__objectType!='opportunity'">{{titleEdit}}</h2>
                <h2 v-if="this.entity.__objectType=='opportunity'">{{entity.name}}</h2>

            </div>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-header', 'end'); ?>
</header>
<?php $this->applyTemplateHook('entity-header', 'after'); ?>
