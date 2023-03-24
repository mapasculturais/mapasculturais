<?php
use MapasCulturais\i;
$this->layout = 'entity';

?>

<div class="form-builder__bg-content form-builder__bg-content--spacing">
    <h4><?= i::__("Categorias de inscrição") ?></h4>
    <span class="subtitle"><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></span>
    <div v-if="phase && phase.registrationCategTitle">
        <entity-field :entity="phase" prop="registrationCategTitle"></entity-field>
    </div>
    <div v-if="phase && phase.registrationCategTitle">
        <entity-field :entity="phase" prop="registrationCategDescription"></entity-field>
    </div>
    <div>
        <p>Categorias</p>
        <input type="text" />
        <button class="button--primary-outline button"><mc-icon name="add"></mc-icon> Adicionar</button>
    </div>
</div>