<?php $this->applyTemplateHook('opportunity-registrations--tables','before'); ?>
<div id="inscritos" class="aba-content">
    <?php $this->applyTemplateHook('opportunity-registrations--tables','begin'); ?>
    <?php if ($entity->canUser('@control')): ?>
        <?php $this->part('singles/opportunity-registrations--tables--manager', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--publish-button', ['entity' => $entity]) ?>

    <?php elseif ($entity->publishedRegistrations): ?>
        <?php $this->part('singles/opportunity-registrations--tables--published', ['entity' => $entity]) ?>
    <?php endif; ?>
    <?php $this->applyTemplateHook('opportunity-registrations--tables','end'); ?>
</div>
<?php $this->applyTemplateHook('opportunity-registrations--tables','after'); ?>
<!--#inscritos-->