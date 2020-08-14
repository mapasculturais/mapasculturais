<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#sobre" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Sobre");?></a></li>

    <li><a href="#inscricoes" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Inscrições");?></a></li>
    <?php if($entity->publishedRegistrations): ?>
        <li><a href="#inscritos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Resultado");?></a></li>
    <?php elseif($entity->canUser('@control')): ?>
        <li><a href="#inscritos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Inscritos");?></a></li>
    <?php endif; ?>

    <?php if(!($this->controller->action === 'create')):?>
    <li><a href="#permissao" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Permissões");?></a></li>
    <?php endif;?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
