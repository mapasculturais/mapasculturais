<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
')
?>
<div class="opportunity-phase-list-registrations__box col-6">
    <div class="opportunity-phase-list-registrations__status col-6">
        <h4 class="bold"><?php i::_e("Resumo das inscrições") ?></h4>
        <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
        <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
        <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
        <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
    </div>
    <div class="col-6 grid-2 opportunity-phase-list-registrations__endbox">
        <div>
            <mc-link :entity="entity" class="button button--primary button--icon" icon="external" route="registrations" right-icon>
                <h4 class="semibold"><?= i::__("Lista de inscrições") ?></h4>
            </mc-link>
        </div>
        <div>
            <button class="button button--primary" @click="sync()"><mc-icon name="sync" ></mc-icon></button>
        </div>
    </div>

</div>