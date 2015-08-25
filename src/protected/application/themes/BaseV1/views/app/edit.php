<?php
$this->layout = 'panel';
?>

<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <form method="post" class="panel-app-form" action="<?php echo $entity->singleUrl; ?>?redirectTo=<?php echo $app->createUrl('panel', 'apps'); ?>">
            <input type="hidden" name="_method" value="put">
            <div>&nbsp;</div>
            <div>&nbsp;</div>
            <div>
                <span class="label">Nome do Aplicativo:</span><br>
                <input name="name" value="<?php echo $entity->name; ?>" class="txt">
            </div>
            <div>
                <span class="label">Chave Pública:</span><br>
                <input type="text" value="<?php echo $entity->publicKey; ?>" disabled="disabled" class="txt small">
            </div>
            <div>
                <span class="label">Chave Privada:</span><br>
                <input type="password" value="<?php echo $entity->privateKey; ?>" disabled="disabled" class="js-input--select-on-click js-input--app-key txt small" data-input-types="['password', 'text']"> <a href="#" class='js-input--app-key--toggle hltip' hltitle="ver/ocultar">!</a>
            </div>
            <input type="submit" value="Atualizar">
        </form>
    </header>
</div>