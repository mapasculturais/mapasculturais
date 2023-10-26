<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-stepper-vertical
    v1-embed-tool
');
?>
<mc-stepper-vertical :items="newPhases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="stepper-header__content">
            <div class="info">
                <h3 class="info__title">{{item.label}}</h3>
                <div v-if="item.type && item.type != ''" class="info__type">
                    <span class="title"> <?= i::__('Tipo') ?>: </span>
                    <span v-if="item.__objectType == 'opportunity' && !item.isLastPhase" class="type"><?= i::__('Coleta de dados') ?></span>
                    <span v-if="item.__objectType == 'evaluationmethodconfiguration'" class="type">{{item.type}}</span>
                </div>
            </div>
        </div>
    </template>
    <template #default="{index, item}">
        <mc-card v-if="item.id">
            <v1-embed-tool route="reportmanager" :id="item.id"></v1-embed-tool>
        </mc-card>
    </template>
</mc-stepper-vertical>