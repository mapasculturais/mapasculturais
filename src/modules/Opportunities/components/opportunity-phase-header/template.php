<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 *
 */

use MapasCulturais\i;
?>
<div :class="classes" class="grid-12 opportunity-phase-header">
    <div class="sm:col-12 opportunity-phase-header__title" :class="titleColClass">
        <h3>{{ title }}</h3>
    </div>
    <div v-if="dateFrom" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data de início") ?></h6>
        <h4>{{ dateFrom }}</h4>
    </div>
    <div v-if="dateTo" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data final") ?></h6>
        <h4>{{ dateTo }}</h4>
    </div>
    <div v-if="publishDate" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data de publicação") ?></h6>
        <h4>{{ publishDate }}</h4>
    </div>
</div>