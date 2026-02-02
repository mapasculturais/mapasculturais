<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<?php $this->applyTemplateHook('second-banner', 'before') ?>
<div v-if="secondBanner" class="home-header__banner">
    <?php $this->applyTemplateHook('second-banner-content', 'begin') ?>
    <a v-if="secondBannerLink" :href="secondBannerLink" :target="secondBannerOpenInNewTab ? '_blank' : null" :rel="secondBannerOpenInNewTab ? 'noopener noreferrer' : null">
        <img :src="secondBanner" :alt="secondBannerAlt || ''" />
    </a>
    <img v-if="!secondBannerLink" :src="secondBanner" :alt="secondBannerAlt || ''" />
    <?php $this->applyTemplateHook('second-banner-content', 'end') ?>
</div>
<?php $this->applyTemplateHook('second-banner', 'after') ?>