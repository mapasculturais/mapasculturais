<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    evaluation-method-simple--apply
');
?>

<div class="col-4 text-right">
    <evaluation-method-simple--apply :entity="phase" :entities="entities"></evaluation-method-simple--apply>
</div>