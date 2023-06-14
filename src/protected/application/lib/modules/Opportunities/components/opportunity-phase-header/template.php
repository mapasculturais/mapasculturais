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
        <h3 class="opportunity__color">{{ title }}</h3>
    </div>
    <div v-if="dateFrom" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data de início") ?></h6>
        <h4 class="opportunity__color bold">{{ dateFrom }}</h4>
    </div>
    <div v-if="dateTo" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data final") ?></h6>
        <h4 class="opportunity__color bold">{{ dateTo }}</h4>
    </div>
    <div v-if="publishDate" class="col-2 sm:col-4">
        <h6 class="bold"><?= i::__("Data de publicação") ?></h6>
        <h4 class="opportunity__color bold">{{ publishDate }}</h4>
    </div>
</div>