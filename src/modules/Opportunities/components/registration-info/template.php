<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div :class="['registration-info', classes]">
    <h3 class="registration-info__title bold"> <?= i::__('Informações da inscrição') ?> </h3>
    <div class="registration-info__content">
        <div class="registration-info__data">
            <h5 class="registration-info__data__title semibold"> <?= i::__('Inscrição') ?> </h5>
            <h4 v-if="registration.number" class="registration-info__data__info bold"> {{registration.number}} </h4>
            <h4 v-if="!registration.number" class="registration-info__data__info bold"> mc-000000000 </h4>
        </div>
        <div class="registration-info__data">
            <h5 class="registration-info__data__title semibold"> <?= i::__('Data') ?> </h5>
            <h4 class="registration-info__data__info bold">{{registration?.createTimestamp?.date('2-digit year')}}</h4>
        </div>
        <div class="registration-info__data">
            <h5 class="registration-info__data__title semibold"> <?= i::__('Categoria') ?> </h5>
            <h4 v-if="registration.category" class="registration-info__data__info bold">{{registration.category}}</h4>
            <h4 v-if="!registration.category" class="registration-info__data__info bold"><?php i::_e('Sem categoria') ?></h4>
        </div>
    </div>
</div>