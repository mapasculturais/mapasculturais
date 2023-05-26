<?php
use MapasCulturais\i;
$this->layout = 'entity';
$this->import('opportunity-form-builder-category-list mc-card')
?>

<!-- class="form-builder__bg-content form-builder__bg-content--spacing" poderia ser um mc-card -->
<mc-card>
    <template #title>
        <div class="card-header">
            <label class="card-header__title"><?= i::__("Categorias de inscrição") ?></label>
            <!-- classe antiga class="subtitle" -->
            <div class="card-header__subtitle"><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></div>
        </div>
    </template>
    <template #default>

        <div class="grid-12">
            <entity-field :entity="entity" prop="registrationCategTitle" classes="col-12"></entity-field>
            <entity-field :entity="entity" prop="registrationCategDescription" classes="col-12"></entity-field>
            <opportunity-form-builder-category-list :entity="entity" class="col-12"></opportunity-form-builder-category-list>
        </div>
    </template>

</mc-card>