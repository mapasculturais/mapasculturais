<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('breadcrumb', 'before') ?>
<nav :class="['mc-breadcrumb', {'mc-breadcrumb__hasCover': cover}]" aria-label="<?= i::__('Breadcrumbs') ?>">
    <?php $this->applyTemplateHook('breadcrumb-list', 'before') ?>
    <ul>
        <?php $this->applyTemplateHook('breadcrumb-list', 'begin') ?>
        <li v-for="item in list">
            <a :href="item.url">
                {{item.label}}
            </a>    
        </li>
        <?php $this->applyTemplateHook('breadcrumb-list', 'end') ?>
    </ul>
    <?php $this->applyTemplateHook('breadcrumb-list', 'after') ?>
</nav>
<?php $this->applyTemplateHook('breadcrumb', 'after') ?>