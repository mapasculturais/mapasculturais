<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    evaluation-method-appeal-phase--apply
');
?>

<div class="col-4 text-right">
    <evaluation-method-appeal-phase--apply :entity="phase" :entities="entities"></evaluation-method-appeal-phase--apply>
</div>