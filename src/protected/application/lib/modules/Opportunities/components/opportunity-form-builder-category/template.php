<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    opportunity-form-builder-category-list
');
?>
<div class="opportunity-category">
    <div class="opportunity-category__header">
            <label class="opportunity-category__title"><?= i::__("Categorias de inscrição") ?></label>
            <div class="opportunity-category__subtitle"><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></div>
        </div>
    <div class="opportunity-category__content">
        <entity-field :entity="entity" prop="registrationCategTitle" classes="col-12"></entity-field>
        <entity-field :entity="entity" prop="registrationCategDescription" classes="col-12"></entity-field>
        <opportunity-form-builder-category-list :entity="entity" class="col-12"></opportunity-form-builder-category-list>
    </div>
</div>