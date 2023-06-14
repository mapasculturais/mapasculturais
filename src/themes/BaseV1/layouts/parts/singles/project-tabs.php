<?php use MapasCulturais\i; ?>
<?php $this->applyTemplateHook('tabs','before'); ?>
<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <?php $this->part('tab', ['id' => 'sobre', 'label' => i::__("Sobre"), 'active' => true]) ?>

    <?php if(!$entity->isNew()): ?>
        <?php $this->part('tab', [
            'id' => 'eventos', 
            'label' => i::__("Status dos eventos"), 
            'properties' => [
                'ng-if' => 'data.entity.userHasControl && data.entity.events.length'
            ]
        ]); ?>
    <?php endif; ?>

    <?php if(!($this->controller->action === 'create')):?>
        <?php $this->part('tab', ['id' => 'permissao', 'label' => i::__("Responsáveis")]) ?>
    <?php endif;?>
    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>
<?php $this->applyTemplateHook('tabs','after'); ?>
