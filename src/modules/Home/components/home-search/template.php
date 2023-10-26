<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>
<div class="home-search">
    <form class="home-search__form">
        <input class="input" type="text" name="search" />
        
        <button class="button" type="submit">
            <mc-icon name="search"></mc-icon>
        </button>    
    </form>

    <button class="button button--primary button--icon filter">
        <mc-icon name="filter"></mc-icon>
        <?php i::_e('Filtrar')?>
    </button>
</div>