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
        <h4 class="bold"><?= i::__("Categorias de inscrição") ?></h4>
        <h6><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></h6>
    </div>

    <div class="opportunity-category__content grid-12">
        <entity-field :entity="entity" @change="autoSave" prop="registrationCategTitle" classes="col-12"></entity-field>
        <entity-field :entity="entity" @change="autoSave"  prop="registrationCategDescription" classes="col-12"></entity-field>
        <opportunity-category-list :entity="entity"  class="col-12"></opportunity-category-list>
    </div>
</div>