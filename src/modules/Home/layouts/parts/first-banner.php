<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<?php $this->applyTemplateHook('first-banner', 'before') ?>
<div v-if="banner" class="home-header__banner">
    <?php $this->applyTemplateHook('first-banner-content', 'begin') ?>
    <a v-if="bannerLink" :href="bannerLink" :target="bannerOpenInNewTab ? '_blank' : null" :rel="bannerOpenInNewTab ? 'noopener noreferrer' : null">
        <img :src="banner" :alt="bannerAlt || ''" />
    </a>
    <img v-if="!bannerLink" :src="banner" :alt="bannerAlt || ''" />
    <?php $this->applyTemplateHook('first-banner-content', 'end') ?><!--  -->
</div>
<?php $this->applyTemplateHook('first-banner', 'after') ?>