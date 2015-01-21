<?php
$this->enqueueScript('app', 'page', 'js/page.js', array('mapasculturais'));
?>
<nav id="nav-da-pagina" class="sidebar-panel">
    <?php echo $left ?>
</nav>
<div class="panel-main-content">
    <?php echo $content ?>
</div>
<!--.panel-main-content-->