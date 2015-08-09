<?php
$this->layout = 'panel';
?>

<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2>Novo App</h2>
        <form method="post" action="<?php echo $entity->singleUrl; ?>?redirectTo=<?php echo $app->createUrl('panel', 'apps'); ?>">
            <input type="hidden" name="_method" value="put">
            <div>&nbsp;</div>
            <div>&nbsp;</div>
            <div>
                <span class="label">Nome:</span>
                <input name="name" value="<?php echo $entity->name; ?>">
            </div>
            <div>
                <span class="label">Chave Pública:</span>
                <input type="readonly" value="<?php echo $entity->publicKey; ?>">
            </div>
            <div>
                <span class="label">Chave Privada:</span>
                <input type="password" value="<?php echo $entity->privateKey; ?>">
            </div>
            <input type="submit" value="Atualizar">
        </form>
    </header>
</div>