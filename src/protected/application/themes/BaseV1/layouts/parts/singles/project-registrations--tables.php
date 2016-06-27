<div ng-if="data.projectRegistrationsEnabled" id="inscritos" class="aba-content">
    <?php if ($entity->canUser('@control')): ?>
        <?php $this->part('singles/project-registrations--tables--manager', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-registrations--publish-button', ['entity' => $entity]) ?>

    <?php elseif ($entity->publishedRegistrations): ?>
        <?php $this->part('singles/project-registrations--tables--published', ['entity' => $entity]) ?>
    <?php endif; ?>
</div>
<!--#inscritos-->