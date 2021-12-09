<?php
use MapasCulturais\i;
?>

<div ng-if="data.step == 2 && newPhasePostData.hasAccountability || data.step == 'accountability'" class="alert info">
    <strong> <?php i::_e('ATENÇÃO'); ?></strong>
    <p><?php i::_e('- Após criar e publicar a prestação de contas, esta ação <strong>não</strong> poderá ser desfeita.'); ?></p>
    <p><?php i::_e('- Após criar a prestação de contas <strong>não</strong> será mais possível criar fases.'); ?></p>
</div>