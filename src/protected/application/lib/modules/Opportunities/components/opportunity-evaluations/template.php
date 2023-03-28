<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    v1-embed-tool
')
?>

<div class="opportunity-evaluations__content">
    <div class="grid-12 opportunity-evaluations__bg-content">
        <div class="col-8 sm:col-12 opportunity-evaluations__title">
            <p class="opportunity__color">{{ entity.name }}</p>
        </div>
        <div class="col-4 sm:col-6 opportunity-evaluations__type">
            <label><?= i::__('Tipo') ?></label>: <label class="phase-stepper__type--item">{{entity.type.name}}</label>
        </div>
    </div>

    <div class="grid-12">
        <div class="col-12">
            <v1-embed-tool route="evaluationmenager" :id="entity.id"></v1-embed-tool>
        </div>
    </div>
</div>