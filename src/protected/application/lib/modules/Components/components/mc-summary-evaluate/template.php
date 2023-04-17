<div class="mc-summary-evaluate">
    <div class="mc-summary-evaluate__pending">
        <h4><?= MapasCulturais\i::__('Avaliações pendente') ?></h4>
        <span>{{summary.pending}} <?= MapasCulturais\i::__('avaliações disponíveis') ?></span>
    </div>

    <div class="mc-summary-evaluate__started">
        <h4><?= MapasCulturais\i::__('Avaliações iniciadas') ?></h4>
        <span>{{summary.started}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>

    <div class="mc-summary-evaluate__completed">
        <h4><?= MapasCulturais\i::__('Avaliações concluídas') ?></h4>
        <span>{{summary.completed}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>

    <div class="mc-summary-evaluate__sent">
        <h4><?= MapasCulturais\i::__('Avaliações enviadas') ?></h4>
        <span>{{summary.send}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>
</div>