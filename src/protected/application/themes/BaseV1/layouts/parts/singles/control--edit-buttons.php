<!-- se estiver na página de edição e logado mostrar:-->
<?php if ($this->controller->action !== 'create' && $this->controller->id !== 'registration'): ?>
    <a href="<?php echo $entity->singleUrl?>" class="btn btn-default js-toggle-edit">Sair do modo de edição</a>
<?php endif; ?>

<?php if($entity->usesDraft()): ?>
    <?php if($entity->isNew() || $entity->status === $status_draft):  ?>
        <a class="btn btn-default js-submit-button hltip" data-status="<?php echo $status_draft ?>"  hltitle="Salvar este <?php echo strtolower($entity->getEntityTypeLabel()) ?> como rascunho.">Salvar rascunho</a>
        <a class="btn btn-primary js-submit-button hltip" data-status="<?php echo $status_enabled ?>" hltitle="Salvar e publicar este <?php echo strtolower($entity->getEntityTypeLabel()) ?>.">Publicar</a>

    <?php else: ?>
        <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>">Salvar</a>
    <?php endif; ?>

<?php elseif($this->controller->id === 'registration'): ?>
    <a class="btn btn-primary js-submit-button">Salvar</a>

<?php else: ?>
    <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>">Salvar</a>
<?php endif; ?>

<script type="text/javascript">
    MapasCulturais.Messages.help('Os ícones de lápis indicam conteúdos editáveis.');
</script>
