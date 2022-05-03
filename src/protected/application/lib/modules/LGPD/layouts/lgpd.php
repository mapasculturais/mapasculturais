<?php
$this->part('header', $render_data);
$config = $app->config['module.LGPD'];
?> 
<nav id="nav-da-pagina" class="sidebar-panel">
    <ul>
        <?php foreach($config as $key => $value): ?>
            <li>
                <a href="<?= $app->createUrl('lgpd', 'accept', [$key]) ?>"><?= $value['title'] ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
<div class="panel-main-content">
    <?php echo $TEMPLATE_CONTENT ?>
</div>
<?php
$this->part('footer', $render_data);

