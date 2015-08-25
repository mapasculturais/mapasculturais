<?php
$this->layout = 'panel';

?>

<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2>Novo App</h2>
        <br><br>
        <form method="post" action="<?php echo $app->createUrl('app', 'index'); ?>?redirectTo=<?php echo $app->createUrl('panel', 'apps'); ?>">
            <input name="name" placeholder="Nome do Aplicativo">
            <input type="submit" value="Criar">
        </form>
    </header>
</div>