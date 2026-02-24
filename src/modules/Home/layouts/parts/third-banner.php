<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<?php $this->applyTemplateHook('third-banner', 'before') ?>
<div v-if="thirdBanner" class="home-header__banner">
    <?php $this->applyTemplateHook('third-banner-content', 'begin') ?>
    <a v-if="thirdBannerLink" :href="thirdBannerLink" :target="thirdBannerOpenInNewTab ? '_blank' : null" :rel="thirdBannerOpenInNewTab ? 'noopener noreferrer' : null">
        <img :src="thirdBanner" :alt="thirdBannerAlt || ''" />
    </a>
    <img v-if="!thirdBannerLink" :src="thirdBanner" :alt="thirdBannerAlt || ''" />
    <?php $this->applyTemplateHook('third-banner-content', 'end') ?>
</div>
<?php $this->applyTemplateHook('third-banner', 'after') ?>