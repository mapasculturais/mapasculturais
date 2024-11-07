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
        <div v-if="entity.summary.registrations">
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas/pendentes</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Approved"><?= i::__("Quantidade de inscrições <strong>selecionadas</strong>:") ?> <strong>{{entity.summary.Approved}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Notapproved"><?= i::__("Quantidade de inscrições <strong>não selecionadas</strong>:") ?> <strong>{{entity.summary.Notapproved}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Waitlist"><?= i::__("Quantidade de inscrições <strong>suplentes</strong>:") ?> <strong>{{entity.summary.Waitlist}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Invalid"><?= i::__("Quantidade de inscrições <strong>inválida</strong>:") ?> <strong>{{entity.summary.Invalid}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
        </div>

        <div v-if="!entity.summary.registrations && !entity.isFirstPhase">
            <?= i::__("As inscrições para esta fase ainda não estão disponíveis") ?>
        </div>

        <div v-if="!entity.summary.registrations && entity.isFirstPhase">
            <?= i::__("Não existem inscrições cadastradas") ?>
        </div>
    </div>
    <div class="col-6 grid-2 opportunity-phase-list-registrations__endbox">
        <div>
            <mc-link :entity="entity" class="button button--primary button--icon" :class="{'disabled': !entity.summary.registrations}" icon="external" route="registrations" right-icon>
                <h4 class="semibold"><?= i::__("Lista de inscrições") ?></h4>
            </mc-link>
        </div>
        <div>
            <button v-if="!entity.isFirstPhase" class="button button--primary" @click="sync()" title="<?= i::__("Sincronizar inscrições") ?>"><mc-icon name="sync"></mc-icon></button>
        </div>
    </div>

</div>