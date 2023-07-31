<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-stepper-vertical
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="stepper-header__content">
            <div class="info">
                <h3 v-if="index" class="info__title">{{item.name}}</h3>
                <h3 v-if="!index" class="info__title"><?= i::__('Período de inscrição') ?></h3>
                <div v-if="item.type && item.type != ''" class="info__type">
                    <span class="title"> <?= i::__('Tipo') ?>: </span>
                    <span class="type"><?= i::__('Coleta de dados') ?></span>
                </div>
            </div>
        </div>
    </template>
    <template #header-actions="{index, item}">
        <mc-link route="support/list" :params="[item.id]" class="button button--primary button-support"> <?= i::__('Realizar suporte') ?> </mc-link>
    </template>
</mc-stepper-vertical>