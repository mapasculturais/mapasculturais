<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-loading
');
?>
<mc-loading :condition="loading"></mc-loading>
<slot v-if="!loading" :entity="entity"></slot> 