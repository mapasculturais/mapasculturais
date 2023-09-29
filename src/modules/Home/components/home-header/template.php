<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    home-search
');

?>
<div :class="['home-header', {'home-header--withBanner' : banner}] ">
    <div class="home-header__content">

        <div v-if="title || description" class="home-header__main">
            <label v-if="title" class="home-header__title">
                {{title}}
            </label>
            <p v-if="description" class="home-header__description">
                {{description}}
            </p>
        </div>

        <div v-if="banner" class="home-header__banner">
            <a v-if="bannerLink" :href="bannerLink" :download="downloadableLink ? 'download' : null" :target="!downloadableLink ? '_blank' : null">
                <img :src="banner" />
            </a>
            <img v-if="!bannerLink" :src="banner" />
        </div>
  
    </div>
    <div class="home-header__background">
        <div class="img">
            <img :src="background" />
        </div>
    </div>
    <!-- <home-search></home-search> -->
</div>