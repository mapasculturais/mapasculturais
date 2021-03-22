<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#main-content" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Principal");?></a></li>

    <?php if($this->isEditable()): ?>
        <li><a href="#form-config" rel="noopener noreferrer"><?php \MapasCulturais\i::_e("Configuração do Formulário"); ?></a></li>
        <?php if(!$entity->isNew()): ?>
            <li><a href="#evaluations-config" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Configuração da Avaliação");?></a></li>
        <?php endif; ?>
    <?php else: ?>

        <?php if($entity->publishedRegistrations): ?>
            <li><a href="#inscritos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Resultado");?></a></li>
        <?php elseif($entity->canUser('@control')): ?>
            <li><a href="#inscritos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Inscritos");?></a></li>
        <?php endif; ?>

        <?php if($entity->canUser('viewEvaluations') || $entity->canUser('@control')): ?>
            <li><a href="#evaluations" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Avaliações");?></a></li>
        <?php endif; ?>

    <?php endif; ?>

    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
