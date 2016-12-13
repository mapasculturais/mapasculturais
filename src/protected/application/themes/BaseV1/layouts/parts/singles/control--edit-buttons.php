<!-- se estiver na página de edição e logado mostrar:-->
<?php if ($this->controller->action !== 'create' && $this->controller->id !== 'registration'): ?>
    <a href="<?php echo $entity->singleUrl?>" class="btn btn-default js-toggle-edit"><?php \MapasCulturais\i::_e("Sair do modo de edição");?></a>
<?php endif; ?>

<?php if($entity->usesDraft()): ?>
    <?php if($entity->isNew() || $entity->status === $status_draft):  ?>
        <a class="btn btn-default js-submit-button hltip" data-status="<?php echo $status_draft ?>"  hltitle="<?php printf(\MapasCulturais\i::esc_attr__('Salvar este %s como rascunho.'), $entity->getEntityTypeLabel()); ?>"><?php \MapasCulturais\i::_e("Salvar rascunho");?></a>
        <a class="btn btn-primary js-submit-button hltip" data-status="<?php echo $status_enabled ?>" hltitle="<?php printf(\MapasCulturais\i::esc_attr__('Salvar e publicar este %s.'), $entity->getEntityTypeLabel()); ?>"><?php \MapasCulturais\i::_e("Publicar");?></a>
        
        <?php if ($this->controller->action === 'create'): ?>
            <a class="btn btn-warning" href="<?php echo $app->createUrl('panel',$this->controller->id . 's'); ?>"><?php \MapasCulturais\i::_e("Cancelar");?></a>
        <?php endif; ?>
    <?php else: ?>
        <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>"><?php \MapasCulturais\i::_e("Salvar");?></a>

    <?php endif; ?>

<?php elseif($this->controller->id === 'registration'): ?>
    <a class="btn btn-primary js-submit-button"><?php \MapasCulturais\i::_e("Salvar");?></a>

<?php else: ?>
    <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>"><?php \MapasCulturais\i::_e("Salvar");?></a>

<?php endif; ?>

<script type="text/javascript">
    MapasCulturais.Messages.help('<?php \MapasCulturais\i::esc_attr_e("Os ícones de lápis indicam conteúdos editáveis.");?>');
</script>
