<?php
$this->import('entity');
$this->addRequestedEntityToJs();
$this->jsObject['opportunity'] = $entity->opportunity;
$this->useOpportunityAPI();
?>
<?php $this->part('header', $render_data) ?>
<?php $this->part('main-header', $render_data) ?>
<entity #default="{entity}">
<?= $TEMPLATE_CONTENT ?>
</entity>
<?php $this->part('footer', $render_data); 