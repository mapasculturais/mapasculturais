<div id="inscritos" class="aba-content">
    <?php if ($entity->canUser('@control')): ?>
        <?php $this->part('singles/opportunity-registrations--tables--manager', ['entity' => $entity]) ?>
    <?php elseif ($entity->publishedRegistrations): ?>
        <?php $this->part('singles/opportunity-registrations--tables--published', ['entity' => $entity]) ?>
    <?php endif; ?>
</div>
<!--#inscritos-->