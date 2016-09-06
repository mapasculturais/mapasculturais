<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <li class="active"><a href="#sobre">Sobre</a></li>

    <li ng-if="data.projectRegistrationsEnabled"><a href="#inscricoes">Inscrições</a></li>
    <?php if($entity->publishedRegistrations): ?>
        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Resultado</a></li>
    <?php elseif($entity->canUser('@control')): ?>
        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Inscritos</a></li>
    <?php endif; ?>

    <?php if(!$entity->isNew()): ?>
        <li ng-if="data.entity.userHasControl && data.entity.events.length" ><a href="#eventos">Status dos eventos</a></li>
    <?php endif; ?>

    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
