<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>

<div class="entity-lock" v-if="entityLock">
   <h3>{{prefix}} <?= i::__('já esta sendo editada por') ?> {{entityLock.agent.name}}</h3>
   <p><?= i::__('Desde:') ?> {{formatDate(entityLock.lockTimestamp)}}</p>
   <button class="button button--primary" @click="unlock()"><?= i::__('Assumir controle') ?></button>
</div>