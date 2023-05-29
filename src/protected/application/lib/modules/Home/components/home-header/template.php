<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    home-search
');
?>
<div class="home-header">
    <div class="home-header__content">
        <div class="home-header__content--left">
            <div class="content">
                <label class="content__title">
                    {{title}}
                </label>
                <p class="content__description">
                    {{description}}
                </p>
            </div>
        </div>

        <div class="home-header__content--right">
            <div class="img">
                <!-- <img src="<?php $this->asset('img/home/home-header/home-header.png') ?>" /> -->
                <img src="<?php $this->asset('img/home/home-header/home-header2.jpg') ?>" />
            </div>
        </div>
    </div>
    <!-- <home-search></home-search> -->
</div>