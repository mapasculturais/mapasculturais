<?php $app->enqueueScript('app', 'page', '/js/page.js', array('mapasculturais')) ?>
<nav id="nav-da-pagina" class="barra-esquerda barra-lateral">
    <?php echo $left ?>
</nav>
<!--#nav-do-painel-->
<div class="main-content">
    <?php echo $content ?>
</div>
<!--.main-content-->

<div class="barra-direita barra-lateral">
    <?php echo $right ?>
</div>