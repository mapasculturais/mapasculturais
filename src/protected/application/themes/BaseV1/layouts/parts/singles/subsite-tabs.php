<?php $this->applyTemplateHook('tabs','before'); ?>

<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#filtros" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Filtros'); ?></a></li>
    <li><a href="#texts" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Textos'); ?></a></li>
    <li><a href="#entidades" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Entidades'); ?></a></li>
    <li><a href="#imagens" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Imagens'); ?></a></li>
    <li><a href="#mapa" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Mapa'); ?></a></li>
    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>

<?php $this->applyTemplateHook('tabs','after'); ?>
