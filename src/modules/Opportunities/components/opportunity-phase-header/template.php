<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>
<div class="grid-12 form-builder__bg-content opportunity-phase-header">
    <div class="sm:col-12 form-builder__title" :class="titleColClass">
        <p class="opportunity__color">{{ title }}</p>
    </div>
    <div v-if="dateFrom" class="col-2 sm:col-4 form-builder__period">
        <h5 class="period_label"><?= i::__("Data de início") ?></h5>
        <h5 class="opportunity__color">{{ dateFrom }}</h5>
    </div>
    <div v-if="dateTo" class="col-2 sm:col-4 form-builder__period">
        <h5 class="period_label"><?= i::__("Data final") ?></h5>
        <h5 class="opportunity__color">{{ dateTo }}</h5>
    </div>
    <div v-if="publishDate" class="col-2 sm:col-4 form-builder__period">
        <h5 class="period_label"><?= i::__("Data de publicação") ?></h5>
        <h5 class="opportunity__color">{{ publishDate }}</h5>
    </div>
</div>