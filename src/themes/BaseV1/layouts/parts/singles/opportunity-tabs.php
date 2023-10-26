<?php use MapasCulturais\i; ?>
<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <?php $this->part('tab', ['id' => 'main-content', 'label' => i::__("Principal"), 'active' => true]) ?>

    <?php if($this->isEditable()): ?>
        <?php $this->part('tab', ['id' => 'form-config', 'label' => i::__("Configuração do Formulário")]) ?>
        <?php if(!$entity->isNew()): ?>
            <?php $this->part('tab', ['id' => 'evaluations-config', 'label' => i::__("Configuração da Avaliação")]) ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if($entity->publishedRegistrations): ?>
            <?php $this->part('tab', ['id' => 'inscritos', 'label' => i::__("Resultado")]) ?>
        <?php elseif($entity->canUser('@control')): ?>
            <?php $this->part('tab', ['id' => 'inscritos', 'label' => i::__("Inscritos")]) ?>
        <?php endif; ?>

        <?php if($entity->canUser('viewEvaluations') || $entity->canUser('@control')): ?>
            <?php $this->part('tab', ['id' => 'evaluations', 'label' => i::__("Avaliações")]) ?>
        <?php endif; ?>

    <?php endif; ?>

    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
