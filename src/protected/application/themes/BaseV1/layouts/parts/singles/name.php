<?php $this->applyTemplateHook('name','before'); ?>
<h2><span class="js-editable" data-edit="name" data-original-title="Nombre para mostrar" data-emptytext="Nombre para mostrar"><?php echo $entity->name; ?></span></h2>
<?php $this->applyTemplateHook('name','after'); ?>