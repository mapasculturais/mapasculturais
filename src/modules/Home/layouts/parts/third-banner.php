<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<?php $this->applyTemplateHook('third-banner', 'before') ?>
<div v-if="thirdBanner" class="home-header__banner">
    <?php $this->applyTemplateHook('third-banner-content', 'begin') ?>
    <a v-if="thirdBannerLink" :href="thirdBannerLink" :download="thirdDownloadableLink ? '' : undefined" :target="!thirdDownloadableLink ? '_blank' : null">
        <img :src="thirdBanner" />
    </a>
    <img v-if="!thirdBannerLink" :src="thirdBanner" />
    <?php $this->applyTemplateHook('third-banner-content', 'end') ?>
</div>
<?php $this->applyTemplateHook('third-banner', 'after') ?>