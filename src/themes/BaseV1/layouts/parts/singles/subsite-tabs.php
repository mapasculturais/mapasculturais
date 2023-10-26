<?php use MapasCulturais\i; ?>
<?php $this->applyTemplateHook('tabs','before'); ?>

<ul class="abas clearfix">
    <?php $this->applyTemplateHook('tabs','begin'); ?>
    <?php $this->part('tab', ['id' => 'filtros', 'label' => i::__("Filtros"), 'active' => true]) ?>
    <?php $this->part('tab', ['id' => 'texts', 'label' => i::__("Textos")]) ?>
    <?php $this->part('tab', ['id' => 'entidades', 'label' => i::__("Entidades")]) ?>
    <?php $this->part('tab', ['id' => 'imagens', 'label' => i::__("Imagens")]) ?>
    <?php $this->part('tab', ['id' => 'mapa', 'label' => i::__("Mapa")]) ?>
    <?php $this->applyTemplateHook('tabs','end'); ?>
</ul>

<?php $this->applyTemplateHook('tabs','after'); ?>
