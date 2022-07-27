<?php
use MapasCulturais\i;

?>

<div class="home-header">

    <div class="home-header__left">
        <div class="home-header__left--content">
            <label class="title">
                {{title}}
            </label>
            <p class="description">
                {{description}}
            </p>
        </div>
    </div>

    <div class="home-header__right">
        <div class="img">
            <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
        </div>
    </div>

</div>