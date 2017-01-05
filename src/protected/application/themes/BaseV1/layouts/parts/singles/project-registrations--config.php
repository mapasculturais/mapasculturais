<div ng-if="data.projectRegistrationsEnabled" id="inscricoes" class="aba-content">
    <?php if ($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <p ng-if="data.isEditable" class="alert info">
            <?php \MapasCulturais\i::_e("Utilize este espaço caso queira abrir inscrições para Agentes Culturais cadastrados na plataforma.");?>
            <span class="close"></span>
        </p>
    <?php endif; ?>

    <?php $this->part('singles/project-registrations--user-registrations', ['entity' => $entity]) ?>

    <?php $this->part('singles/project-registrations--intro', ['entity' => $entity]); ?>

    <?php $this->part('singles/project-registrations--rules', ['entity' => $entity]); ?>

    <?php if ($this->isEditable()): ?>

        <?php $this->part('singles/project-registrations--categories', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-registrations--agent-relations', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/project-registrations--seals', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-registrations--fields', ['entity' => $entity]) ?>

    <?php endif; ?>

    <?php $this->part('singles/project-registrations--form', ['entity' => $entity]) ?>
</div>
<!--#inscricoes-->
