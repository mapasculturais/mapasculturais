<!-- se estiver na página de edição e logado mostrar:-->
<?php if ($this->controller->action !== 'create' && $this->controller->id !== 'registration'): ?>
    <a href="<?php echo $entity->singleUrl?>" class="btn btn-default js-toggle-edit"><?php \MapasCulturais\i::_e("Sair do modo de edição");?></a>
<?php endif; ?>

<?php if($entity->usesDraft()): ?>
    <?php if($entity->isNew() || $entity->status === $status_draft):  ?>
        <a class="btn btn-default js-submit-button hltip" data-status="<?php echo $status_draft ?>"  hltitle="<?php \MapasCulturais\i::_e("Salvar este");?> <?php echo strtolower($entity->getEntityTypeLabel()) ?> <?php \MapasCulturais\i::_e("como rascunho.");?>"><?php \MapasCulturais\i::_e("Salvar rascunho");?></a>
        <a class="btn btn-primary js-submit-button hltip" data-status="<?php echo $status_enabled ?>" hltitle="<?php \MapasCulturais\i::_e("Salvar e publicar este");?> <?php echo strtolower($entity->getEntityTypeLabel()) ?>."><?php \MapasCulturais\i::_e("Publicar");?></a>

    <?php else: ?>
        <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>"><?php \MapasCulturais\i::_e("Salvar");?></a>
    <?php endif; ?>

<?php elseif($this->controller->id === 'registration'): ?>
    <a class="btn btn-primary js-submit-button"><?php \MapasCulturais\i::_e("Salvar");?></a>

<?php else: ?>
    <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>"><?php \MapasCulturais\i::_e("Salvar");?></a>
<?php endif; ?>

<script type="text/javascript">
    MapasCulturais.Messages.help('<?php \MapasCulturais\i::_e("Os ícones de lápis indicam conteúdos editáveis.");?>');
</script>
