<?php
use MapasCulturais\i;

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
                <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
            </div>
        </div>
    </div>
</div>