<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#sobre"><?php \MapasCulturais\i::_e("Sobre");?></a></li>

    <li ng-if="data.projectRegistrationsEnabled"><a href="#inscricoes"><?php \MapasCulturais\i::_e("Inscrições");?></a></li>
    <?php if($entity->publishedRegistrations): ?>
        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos"><?php \MapasCulturais\i::_e("Resultado");?></a></li>
    <?php elseif($entity->canUser('@control')): ?>
        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos"><?php \MapasCulturais\i::_e("Inscritos");?></a></li>
    <?php endif; ?>

    <?php if(!($this->controller->action === 'create')):?>
    <li><a href="#permissao"><?php \MapasCulturais\i::_e("Permissões");?></a></li>
    <?php endif;?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
