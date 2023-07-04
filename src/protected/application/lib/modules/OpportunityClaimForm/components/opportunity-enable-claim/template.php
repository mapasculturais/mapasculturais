<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
');
?>

<div class="opportunity-enable-claim">
    <h4 class="bold opportunity-enable-claim__title">Recurso</h4>
    <div class="opportunity-enable-claim__input ">
        <input type="checkbox" id="resource" v-model="isActiveClaim"/>
        <label for="resource"><?= i::__("Habilitar Recurso") ?></label>
    </div>
    <div v-if="isActiveClaim" class="opportunity-enable-claim__email">
        <label class="opportunity-enable-claim__label" for="input">
            <h5 class="semibold opportunity-enable-claim__subtitle"><?= i::__("Insira o email que receberá as solicitações") ?></h5>
        </label>
        <div class="opportunity-enable-claim__save">
            <input type="text" v-model="entity.claimEmail" /> <button @click="entity.save()" class="button-popover button button--primary button--primary-outline"><?= i::__("Salvar") ?></button>
        </div>
    </div>
</div>