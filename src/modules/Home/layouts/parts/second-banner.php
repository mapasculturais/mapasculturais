<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<?php $this->applyTemplateHook('second-banner', 'before') ?>
<div v-if="secondBanner" class="home-header__banner">
    <?php $this->applyTemplateHook('second-banner-content', 'begin') ?>
    <a v-if="secondBannerLink" :href="secondBannerLink" :download="secondDownloadableLink ? '' : undefined" :target="!secondDownloadableLink ? '_blank' : null">
        <img :src="secondBanner" />
    </a>
    <img v-if="!secondBannerLink" :src="secondBanner" />
    <?php $this->applyTemplateHook('second-banner-content', 'end') ?>
</div>
<?php $this->applyTemplateHook('second-banner', 'after') ?>