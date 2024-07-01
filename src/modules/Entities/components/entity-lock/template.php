<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>

<div v-if="entityLock">
   <p>{{entityLock.agent.name}} <?= i::__('já está editando') ?></p>
   <p><?= i::__('Desde:') ?> {{formatDate(entityLock.lockTimestamp)}}</p>
   <button class="button button--primary" @click="unlock()"><?= i::__('Assumir controle') ?></button>
</div>