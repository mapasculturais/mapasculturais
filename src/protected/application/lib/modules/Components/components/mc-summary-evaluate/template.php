<div>
    <div>
        <h4><?= MapasCulturais\i::__('Avaliações pendente') ?></h4>
        <span>{{summary.pending}} <?= MapasCulturais\i::__('avaliações disponíveis') ?></span>
    </div>

    <div>
        <h4><?= MapasCulturais\i::__('Avaliações iniciadas') ?></h4>
        <span>{{summary.started}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>

    <div>
        <h4><?= MapasCulturais\i::__('Avaliações concluídas') ?></h4>
        <span>{{summary.completed}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>

    <div>
        <h4><?= MapasCulturais\i::__('Avaliações enviadas') ?></h4>
        <span>{{summary.send}} <?= MapasCulturais\i::__('avaliações') ?></span>
    </div>
</div>