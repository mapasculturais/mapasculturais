<?php
$this->part('header', $render_data);
$config = $app->config['module.LGPD'];
?> 
<nav id="nav-da-pagina" class="sidebar-panel">
    <?php foreach($config as $key => $value): ?>
    <a href="<?= $app->createUrl('lgpd', 'acept', [$key]) ?>"><?= $value['title'] ?></a>
    <?php endforeach; ?>
</nav>
<div class="panel-main-content">
    <?php echo $TEMPLATE_CONTENT ?>
</div>
<?php
$this->part('footer', $render_data);

