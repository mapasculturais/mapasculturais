<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    registration-results
');
?>

<section v-if="hasPreviousEvaluation" class="col-12 grid-12 section">
    <h3 class="col-12"><?php i::_e('Avaliação da fase anterior') ?></h3>

    <div class="section__content col-12">
        <div class="card owner">
            <registration-results :registration="registration" :phase="phase"></registration-results>
        </div>
    </div>
</section>
