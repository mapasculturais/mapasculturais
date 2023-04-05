<?php
use MapasCulturais\i;
$this->layout = 'entity';
$this->import('opportunity-form-builder-category-list mapas-card')
?>

<!-- class="form-builder__bg-content form-builder__bg-content--spacing" poderia ser um mc-card -->
<mapas-card>
    <template #title>
        <div class="category-header">
            <label class="category-header__title"><?= i::__("Categorias de inscrição") ?></label>
            <!-- classe antiga class="subtitle" -->
            <div class="category-header__subtitle"><?= i::__("Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, \"categorias\" ou \"modalidades\".") ?></div>
        </div>
            

    </template>
    <template #default>

        <div class="grid-12">
            <!-- </header -->
            <entity-field :entity="entity" prop="registrationCategTitle" classes="col-12"></entity-field>

            <entity-field :entity="entity" prop="registrationCategDescription" classes="col-12"></entity-field>

            <opportunity-form-builder-category-list class="col-12"></opportunity-form-builder-category-list>
        </div>
    </template>

</mapas-card>