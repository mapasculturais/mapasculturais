<?php
use MapasCulturais\i;

$this->layout = 'panel';

eval(\psy\sh());
?>
<div class="panel-main-content">
    <h1><?php i::_e('Remover Conta') ?></h1>
    <p><?php i::_e('Texto explicando o que acontece com as infos: Aliquam dictum ut risus ut mollis. Morbi quis sem vitae ex volutpat feugiat ut semper metus. Aliquam iaculis congue mi, ac tempus leo semper quis. Duis at tristique tellus, sed posuere ante. Sed tincidunt egestas rhoncus. Nunc faucibus, ligula at gravida sagittis, mauris est gravida est, non elementum felis nibh eget mauris. Maecenas rhoncus ornare elit eget maximus. Donec bibendum convallis turpis, a cursus nisl fringilla vel.')?></p>
    <p>
        <a href="<?php echo $this->controller->createUrl('index') ?>" class="btn btn-success"><?php i::_e('Cancelar') ?></a>
        <a href="<?php echo $app->createUrl('user', 'deleteAccount', ['token' => $app->user->deleteAccountToken]) ?>" class="btn btn-danger"><?php i::_e('Remover Conta') ?></a>
    </p>
</div>
