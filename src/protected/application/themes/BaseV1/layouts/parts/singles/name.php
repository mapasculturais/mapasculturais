<?php $this->applyTemplateHook('name','before'); ?>
<h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
<?php $this->applyTemplateHook('name','after'); ?>