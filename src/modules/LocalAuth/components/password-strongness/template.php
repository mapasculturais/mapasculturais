<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

?>
<div class="progressbar">
    <span> <?= i::__('Força da senha'); ?> </span>
    <progress id="progress" :class="strongnessClass()" :value="strongness()" max="100"></progress>
    <span id="progresslabel">{{strongness()}}%</span>
</div>

<div v-if="getErrors()" class="password-rules">
    <?= i::__('A senha deve conter:') ?>
    <strong> {{getErrors()}}</strong>
</div>