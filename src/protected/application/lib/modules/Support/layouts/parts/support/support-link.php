<?php $this->applyTemplateHook('support-link', 'before');?>
<div class="support-link">
    <?php $this->applyTemplateHook('support-link', 'begin');?>
    <?php $url = $app->createUrl('suporte', 'inscricao', [$entity->id]);?>
    <a href="<?=$url?>"><?php \MapasCulturais\i::_e("Acessar página de suporte");?></a>
    <?php $this->applyTemplateHook('support-link', 'end');?>
</div>
<?php $this->applyTemplateHook('support-link', 'after');?>