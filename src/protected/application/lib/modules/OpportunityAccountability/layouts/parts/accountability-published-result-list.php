<?php $this->applyTemplateHook('accountability-published-result-list','before'); ?>
<?php $this->part('singles/opportunity-registrations--tables--published', ['entity' => $entity]) ?>
<?php $this->applyTemplateHook('accountability-published-result-list','after'); ?>