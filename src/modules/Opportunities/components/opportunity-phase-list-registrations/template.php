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
        <h4 class="bold"><?php i::_e("Resumo") ?></h4>
        <div v-if="entity.summary.registrations">
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade total de inscrições:") ?> <strong>{{entity.summary.registrations}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições em <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}} <?= i::__('inscrições') ?></strong></p>
        </div>
        <br>
        <h4 class="bold"><?php i::_e("Status das inscrições enviadas ") ?></h4>
        <div v-if="entity.summary.registrations">
            <p v-if="entity.summary?.Approved"><?= i::__("Quantidade de inscrições <strong>selecionadas</strong>:") ?> <strong>{{entity.summary.Approved}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Waitlist"><?= i::__("Quantidade de inscrições <strong>suplentes</strong>:") ?> <strong>{{entity.summary.Waitlist}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Notapproved"><?= i::__("Quantidade de inscrições <strong>não selecionadas</strong>:") ?> <strong>{{entity.summary.Notapproved}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Invalid"><?= i::__("Quantidade de inscrições <strong>inválida</strong>:") ?> <strong>{{entity.summary.Invalid}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}} <?= i::__('inscrições') ?></strong></p>
        </div>

        <div v-if="!entity.summary.registrations && !entity.isFirstPhase">
            <?= i::__("As inscrições para esta fase ainda não estão disponíveis") ?>
        </div>

        <div v-if="!entity.summary.registrations && entity.isFirstPhase">
            <?= i::__("Não existem inscrições cadastradas") ?>
        </div>
    </div>
    <div class="col-6 opportunity-phase-list-registrations__endbox">
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