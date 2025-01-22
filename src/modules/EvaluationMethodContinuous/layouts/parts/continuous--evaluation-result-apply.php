<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    evaluation-method-continuous--apply
');
?>

<div class="col-4 text-right">
    <evaluation-method-continuous--apply :entity="phase" :entities="entities"></evaluation-method-continuous--apply>
</div>