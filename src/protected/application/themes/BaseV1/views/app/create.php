<?php
$this->layout = 'panel';

?>

<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2>Nueva App</h2>
        <br><br>
        <form method="post" action="<?php echo $app->createUrl('app', 'index'); ?>?redirectTo=<?php echo $app->createUrl('panel', 'apps'); ?>">
            <input name="name" placeholder="Nombre del Aplicativo">
            <input type="submit" value="Crear">
        </form>
    </header>
</div>