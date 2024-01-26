<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
evaluation-method-technical--apply
');
?>

<div class="col-4 text-right">
    <evaluation-method-technical--apply :entity="phase"></evaluation-method-technical--apply>
</div>