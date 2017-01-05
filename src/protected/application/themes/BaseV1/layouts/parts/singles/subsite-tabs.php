<?php $this->applyTemplateHook('tabs','before'); ?>

<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#filtros">Filtros</a></li>
    <li><a href="#texts">Textos</a></li>
    <li><a href="#entidades">Entidades</a></li>
    <li><a href="#imagens">Imagens</a></li>
    <li><a href="#mapa">Mapa</a></li>
    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>

<?php $this->applyTemplateHook('tabs','after'); ?>
