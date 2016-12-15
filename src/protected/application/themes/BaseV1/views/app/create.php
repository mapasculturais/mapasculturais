<?php
$this->layout = 'panel';

?>

<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php \MapasCulturais\i::_e("Novo App");?></h2>
        <br><br>
        <form method="post" action="<?php echo $app->createUrl('app', 'index'); ?>?redirectTo=<?php echo $app->createUrl('panel', 'apps'); ?>">
            <input name="name" placeholder="<?php \MapasCulturais\i::esc_attr_e("Nome do Aplicativo");?>">
            <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e("Criar");?>">
        </form>
    </header>
</div>