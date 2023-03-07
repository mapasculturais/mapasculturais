<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('entity-field')
?>

<div class="form-builder__bg-content form-builder__bg-content--spacing">
    <h4>Categorias de inscrição</h4>
    <span class="subtitle">Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, "categorias" ou "modalidades".</span>
    <div>
        <p>Grupo de categorias</p>
        <input type="text" />
    </div>
    <div>
        <p>Descrição do grupo</p>
        <input type="text" />
    </div>
    <div>
        <p>Categorias</p>
        <input type="text" />
        <button class="button--primary-outline button"><mc-icon name="add"></mc-icon> Adicionar</button>
    </div>
</div>