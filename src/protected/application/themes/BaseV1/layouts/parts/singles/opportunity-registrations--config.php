<div id="inscricoes" class="aba-content">
    <?php if ($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <p ng-if="data.isEditable" class="alert info">
            <?php \MapasCulturais\i::_e("Utilize este espaço caso queira abrir inscrições para Agentes Culturais cadastrados na plataforma.");?>
            <span class="close"></span>
        </p>
    <?php endif; ?>

    <?php $this->part('singles/opportunity-registrations--user-registrations', ['entity' => $entity]) ?>

    <?php $this->part('singles/opportunity-registrations--intro', ['entity' => $entity]); ?>

    <?php $this->part('singles/opportunity-registrations--rules', ['entity' => $entity]); ?>

    <?php if ($this->isEditable()): ?>

        <?php $this->part('singles/opportunity-registrations--categories', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--agent-relations', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/opportunity-registrations--seals', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--fields', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/opportunity-registrations--importexport', ['entity' => $entity]) ?>
        
    <?php endif; ?>

    <?php $this->part('singles/opportunity-registrations--form', ['entity' => $entity]) ?>
</div>
<!--#inscricoes-->
