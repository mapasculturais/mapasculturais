<?php $this->applyTemplateHook('tabs','before'); ?>

<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#filtros"><?php \MapasCulturais\i::_e('Filtros'); ?></a></li>
    <li><a href="#texts"><?php \MapasCulturais\i::_e('Textos'); ?></a></li>
    <li><a href="#entidades"><?php \MapasCulturais\i::_e('Entidades'); ?></a></li>
    <li><a href="#imagens"><?php \MapasCulturais\i::_e('Imagens'); ?></a></li>
    <li><a href="#mapa"><?php \MapasCulturais\i::_e('Mapa'); ?></a></li>
    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>

<?php $this->applyTemplateHook('tabs','after'); ?>
