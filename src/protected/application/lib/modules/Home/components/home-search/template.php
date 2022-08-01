<?php
use MapasCulturais\i;
?>
<div class="home-search">
    <form class="home-search__form">
        <input class="input" type="text" name="search" />
        
        <button class="button" type="submit">
            <iconify icon="ant-design:search-outlined"></iconify>
        </button>    
    </form>

    <button class="button button--primary button--icon filter">
        <iconify icon="ic:baseline-filter-alt"></iconify>
        <?php i::_e('Filtrar')?>
    </button>
</div>