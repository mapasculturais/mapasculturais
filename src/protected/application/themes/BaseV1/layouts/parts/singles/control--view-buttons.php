<?php if ($entity->canUser('modify')): ?>
    <!-- se estiver na página comum e logado mostrar:-->
    <a href="<?php echo $entity->editUrl ?>" class="btn btn-primary js-toggle-edit"><?php \MapasCulturais\i::_e("Editar");?></a>
<?php endif; ?>

<?php if ($entity->canUser('remove') && $entity->status !== $status_trash): ?>
    <a href="<?php echo $entity->deleteUrl ?>" class="btn btn-danger"><?php \MapasCulturais\i::_e("Excluir");?></a>

<?php elseif ($entity->canUser('undelete') && $entity->status === $status_trash): ?>
    <a href="<?php echo $entity->undeleteUrl ?>" class="btn btn-success"><?php \MapasCulturais\i::_e("Recuperar");?></a>

    <?php if($entity->canUser('destroy')): ?>
        <a class="btn btn-danger" href="<?php echo $entity->destroyUrl; ?>"<?php \MapasCulturais\i::esc_attr_e("Excluir Definitivamente");?></a>
    <?php endif; ?>
<?php endif; ?>
<script type="text/javascript">
    MapasCulturais.Messages.alert('<?php printf(\MapasCulturais\i::esc_attr__("Você possui permissão para editar este %s. Use os botões à direita para editar ou excluir."), strtolower($entity->entityTypeLabel()));?>');
</script>
