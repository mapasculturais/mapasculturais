<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
')
?>
<div class="registration-evaluation-info">
    <section v-for="(info, index) in infos" class="registration-evaluation-info__section">
        <div v-if=" showInfo(index)">
            <div class="registration-evaluation-info__header">
                <p v-if="index == 'general'" class="registration-evaluation-info__title semibold"><?= i::__('Informações gerais')?></p>
                <p v-if="index != 'general'" class="registration-evaluation-info__title semibold">{{index}}</p>
                <h5 v-if="!activeItems[index]" class="registration-evaluation-info__toggle" @click="toggle(index)"><?= i::__('Exibir');?> <mc-icon name="arrowPoint-down"></mc-icon> </h5>
                <h5 v-if="activeItems[index]" class="registration-evaluation-info__toggle" @click="toggle(index)"><?= i::__('Ocultar');?> <mc-icon name="arrowPoint-up"></mc-icon> </h5>
            </div>
            <div v-if="activeItems[index]" class="registration-evaluation-info__content">
                <h6>{{info}}</h6>
            </div>
        </div>
    </section>
</div>