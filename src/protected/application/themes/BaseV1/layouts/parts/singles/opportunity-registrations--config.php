<div id="form-config" class="aba-content">
    <?php $this->part('singles/opportunity-registrations--importexport', ['entity' => $entity]) ?>

    <?php if ($this->isEditable()): ?>
        <?php $this->part('singles/opportunity-registrations--agent-relations', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--categories', ['entity' => $entity]) ?>

        <?php
        /**
         * @todo renomear para opportunity
         */
        $this->part('singles/opportunity-registrations--space-relations', ['entity' => $entity])
        ?>

        <?php $this->part('singles/opportunity-registrations--fields', ['entity' => $entity]) ?>

        <p><a href="<?php echo $app->createUrl('registration', 'preview', ['opportunityId' => $entity->id]); ?>" target="_blank" class="btn btn-primary"><?php MapasCulturais\i::_e('Pré-visualizar ficha de inscrição') ?></a></p>
    <?php endif; ?>
</div>