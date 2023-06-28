<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="age-rating" :class="classes">
    <h4 class="age-rating__title"> <?= i::_e("Classificação Etária"); ?> </h4>
    <div class="age-rating__content">
        {{event.classificacaoEtaria}}
    </div>
</div>