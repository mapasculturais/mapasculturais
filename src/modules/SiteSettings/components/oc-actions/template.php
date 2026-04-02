<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="oc-actions" v-if="useActions">
    <button v-if="clearCache" type="button" class="button button--primary" @click="clearCacheExec"><span><?= i::__('Apagar cache') ?></span></button>
    <button class="button button--primary" @click="save()"><span><?= i::__('Salvar') ?></span></button>
</div>