<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    opportunity-category-list 
    mc-card
');
?>
<div class="opportunity-category">
    <div class="opportunity-category__header">
        <h4 class="bold"><?= i::__("Categorias do edital") ?></h4>
    </div>

    <div class="opportunity-category__content grid-12">
        <opportunity-category-list :entity="entity"  class="col-12"></opportunity-category-list>
    </div>
</div>