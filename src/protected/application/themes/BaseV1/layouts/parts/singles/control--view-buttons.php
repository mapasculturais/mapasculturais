<?php if ($entity->canUser('modify')): ?>
    <!-- se estiver na página comum e logado mostrar:-->
    <a href="<?php echo $entity->editUrl ?>" class="btn btn-primary js-toggle-edit">Editar</a>
<?php endif; ?>

<?php if ($entity->canUser('remove') && $entity->status !== $status_trash): ?>
    <a href="<?php echo $entity->deleteUrl ?>" class="btn btn-danger">Excluir</a>

<?php elseif ($entity->canUser('undelete') && $entity->status === $status_trash): ?>
    <a href="<?php echo $entity->undeleteUrl ?>" class="btn btn-success">Recuperar</a>

    <?php if($entity->canUser('destroy')): ?>
        <a class="btn btn-danger" href="<?php echo $entity->destroyUrl; ?>">Excluir Definitivamente</a>
    <?php endif; ?>
<?php endif; ?>
<script type="text/javascript">
    MapasCulturais.Messages.alert('Você possui permissão para editar este <?php echo strtolower($entity->entityType) ?>. Use os botões à direita para editar ou excluir.');
</script>