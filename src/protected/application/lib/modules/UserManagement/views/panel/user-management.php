<?php
use MapasCulturais\i;

$this->import('
    loading,messages,
    card-user-management
');

$profile = $app->user->profile;
?>

<div class="panel__row">
    <h1>
        <iconify icon="mdi:account-multiple-outline"></iconify>
        <?= i::__('Gerenciamento de usuários') ?>
    </h1>
    <a class="panel__help-link" href="#"><?=i::__('Ajuda')?></a>
</div>
<div class="panel__row">
    <p><?=i::__('Gerencia os usuários do sistema')?></p>
</div>

<?php $this->applyTemplateHook('tabs', 'before') ?>
    <card-user-management></card-user-management>
</div>
