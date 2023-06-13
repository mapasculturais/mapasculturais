<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>
<div :class="classes" class="grid-12 form-builder__header opportunity-phase-header">
    <div class="sm:col-12 form-builder__title" :class="titleColClass">
        <h3 class="opportunity__color">{{ title }}</h3>
    </div>
    <div v-if="dateFrom" class="col-2 sm:col-4 form-builder__period">
        <h4 class="form-builder__label"><?= i::__("Data de início") ?></h4>
        <h4 class="form-builder__value opportunity__color">{{ dateFrom }}</h4>
    </div>
    <div v-if="dateTo" class="col-2 sm:col-4 form-builder__period">
        <h4 class="form-builder__label"><?= i::__("Data final") ?></h4>
        <h4 class="form-builder__value opportunity__color">{{ dateTo }}</h4>
    </div>
    <div v-if="publishDate" class="col-2 sm:col-4 form-builder__period">
        <h4 class="form-builder__label"><?= i::__("Data de publicação") ?></h4>
        <h4 class="form-builder__value opportunity__color">{{ publishDate }}</h4>
    </div>
</div>