<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('opportunity-form-builder-category-list')

?>

<div class="form-builder__bg-content form-builder__bg-content--spacing">
    <h4><?= i::__("Categorias de inscrição") ?></h4>
    <span class="subtitle"><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></span>
    <div>
        <entity-field :entity="entity" prop="registrationCategTitle"></entity-field>
    </div>
    <div>
        <entity-field :entity="entity" prop="registrationCategDescription"></entity-field>
    </div>
    <div>
        <opportunity-form-builder-category-list></opportunity-form-builder-category-list>
    </div>
</div>