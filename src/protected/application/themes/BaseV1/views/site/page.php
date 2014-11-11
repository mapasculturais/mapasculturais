<?php
$this->enqueueScript('app', 'page', 'js/page.js', array('mapasculturais'));
?>
<nav id="nav-da-pagina" class="sidebar-left sidebar">
    <?php echo $left ?>
</nav>
<!--#nav-do-painel-->
<div class="main-content">
    <?php echo $content ?>
</div>
<!--.main-content-->

<div class="sidebar-right sidebar">
    <?php echo $right ?>
</div>