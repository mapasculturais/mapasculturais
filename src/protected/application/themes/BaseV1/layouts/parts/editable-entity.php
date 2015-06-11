<?php
if (!$this->isEditable() && !$entity->canUser('modify')){
    ?><div id="editable-entity" class="clearfix sombra js-not-editable" style='display:none; min-height:0; height:42px;'></div><?php
    return;
}

$class = $entity->getClassName();

$status_draft = $class::STATUS_DRAFT;
$status_enabled = $class::STATUS_ENABLED;
$status_trash = $class::STATUS_TRASH;


$can_edit_roles = $this->controller->id == 'agent' && $entity->user->id != $app->user->id && $entity->id == $entity->user->profile->id && $entity->user->canUser('addRole');
if($this->isEditable()){
    $classes = 'editable-entity-edit';
    if($can_edit_roles)
        $classes .= ' can-edit-roles';
}else{
    $classes = 'editable-entity-single';
}
?>

<div id="editable-entity" class="clearfix sombra <?php echo $classes ?>" data-action="<?php echo $action; ?>" data-entity="<?php echo $this->controller->id ?>" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <?php if ($this->isEditable()): ?>
        <script type="text/javascript">
            MapasCulturais.Messages.help('Os ícones de lápis indicam conteúdos editáveis.');
        </script>
        <div class="controles">
            <?php if ($can_edit_roles): ?>
                <div id="funcao-do-agente" class="dropdown dropdown-select">
                    <div class="placeholder js-selected">
                        <?php if ($entity->user->is('superAdmin')): ?>
                            <span data-role="superAdmin"><?php echo $app->getRoleName('superAdmin'); ?></span>
                        <?php elseif ($entity->user->is('admin')): ?>
                            <span data-role="admin"><?php echo $app->getRoleName('admin'); ?></span>
                        <?php elseif ($entity->user->is('staff')): ?>
                            <span data-role="staff"><?php echo $app->getRoleName('staff'); ?></span>
                        <?php else: ?>
                            <span>Normal</span>
                        <?php endif; ?>
                    </div>
                    <div class="submenu-dropdown js-options">
                        <ul>
                            <li>
                                <span>Normal</span>
                            </li>
                            <?php if ($entity->user->canUser('addRoleStaff')): ?>
                                <li data-role="staff">
                                    <span><?php echo $app->getRoleName('staff') ?></span>
                                </li>
                            <?php endif; ?>

                            <?php if ($entity->user->canUser('addRoleAdmin')): ?>
                                <li data-role="admin">
                                    <span><?php echo $app->getRoleName('admin') ?></span>
                                </li>
                            <?php endif; ?>

                            <?php if ($entity->user->canUser('addRoleSuperAdmin')): ?>
                                <li data-role="superAdmin">
                                    <span><?php echo $app->getRoleName('superAdmin') ?></span>
                                </li>
                            <?php endif; ?>

                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- se estiver na página de edição e logado mostrar:-->
            <?php if ($this->controller->action !== 'create' && $this->controller->id !== 'registration'): ?>
                <a href="<?php echo $entity->singleUrl?>" class="btn btn-default js-toggle-edit">Sair do modo de edição</a>
            <?php endif; ?>

            <?php if($entity->usesDraft()): ?>
                <?php if($entity->isNew() || $entity->status <= 0):  ?>
                    <a class="btn btn-default js-submit-button hltip" data-status="<?php echo $status_draft ?>"  hltitle="Salvar este <?php echo strtolower($entity->getEntityType()) ?> como rascunho.">Salvar rascunho</a>
                    <a class="btn btn-primary js-submit-button hltip" data-status="<?php echo $status_enabled ?>" hltitle="Salvar e publicar este <?php echo strtolower($entity->getEntityType()) ?>.">Publicar</a>

                <?php else: ?>
                    <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>">Salvar</a>
                <?php endif; ?>

            <?php elseif($this->controller->id === 'registration'): ?>
                <a class="btn btn-primary js-submit-button">Salvar</a>

            <?php else: ?>
                <a class="btn btn-primary js-submit-button" data-status="<?php echo $status_enabled ?>">Salvar</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <script type="text/javascript">
            MapasCulturais.Messages.alert('Você possui permissão para editar este <?php echo strtolower($entity->entityType) ?>. Use os botões à direita para editar ou excluir.');
        </script>
        <div class="controles">
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
        </div>
    <?php endif; ?>
</div>
