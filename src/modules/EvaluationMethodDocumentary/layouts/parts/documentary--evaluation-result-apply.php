<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    evaluation-method-documentary--apply
');
?>

<div class="col-4 text-right">
    <evaluation-method-documentary--apply :entity="phase" :entities="entities"></evaluation-method-documentary--apply>
</div>