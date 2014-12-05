<?php
if (!$this->isEditable() && !$entity->canUser('modify')){
    ?><div id="editable-entity" class="clearfix sombra js-not-editable" style='display:none; min-height:0; height:42px;'></div><?php
    return;
}
$can_edit_roles = $this->controller->id == 'agent' && $entity->user->id != $app->user->id && $entity->id == $entity->user->profile->id && $entity->user->canUser('addRole');
if($this->isEditable()){
    $classes = 'editable-entity-edit';
    if($can_edit_roles)
        $classes .= ' can-edit-roles';
}else{
    $classes = 'editable-entity-single';
}
?>

<div id="editable-entity" class="clearfix sombra <?php echo $classes ?>" data-action="<?php echo $action; ?>" data-entity="<?php echo $this->controller->id ?>" data-id="<?php echo $entity->id ?>" data-submit-button-selector="#submitButton">
    <?php $this->part('editable-entity-logo') ?>
    <?php if ($this->isEditable()): ?>
        <script type="text/javascript">
            MapasCulturais.Messages.help('Os ícones de lápis indicam conteúdos editáveis.');
        </script>
        <div class="controles">
            <?php if ($can_edit_roles): ?>
                <div id="funcao-do-agente" class="dropdown">
                    <div class="placeholder js-selected"> <?php
                        if ($entity->user->is('superAdmin'))
                            echo '<span data-role="superAdmin">' . $app->getRoleName('superAdmin') . '</span>';
                        elseif ($entity->user->is('admin'))
                            echo '<span data-role="admin">' . $app->getRoleName('admin') . '</span>';
                        elseif ($entity->user->is('staff'))
                            echo '<span data-role="staff">' . $app->getRoleName('staff') . '</span>';
                        else
                            echo "<span>Normal</span>";
                        ?> </div>
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
                <a href="<?php echo $entity->singleUrl?>" class="botao principal js-toggle-edit">Sair do modo de edição</a>
            <?php endif; ?>
            <a id="submitButton" class="botao principal" data-legend="Salvar" data-legend-submitted="salvo">Salvar</a>
        </div>
    <?php elseif ($entity->canUser('modify')): ?>
        <script type="text/javascript">
            MapasCulturais.Messages.alert('Você possui permissão para editar este <?php echo strtolower($entity->entityType) ?>. Use os botões à direita para editar ou excluir.');
        </script>
        <div class="controles">
            <!-- se estiver na página comum e logado mostrar:-->
            <a href="<?php echo $entity->editUrl ?>" class="botao principal js-toggle-edit">Editar</a>

            <?php if ($entity->canUser('remove') && $entity->status > 0): ?>
                <a href="<?php echo $entity->deleteUrl ?>" class="botao selected">Excluir</a>
            <?php elseif ($entity->canUser('undelete') && $entity->status < 0): ?>
                <a href="<?php echo $entity->undeleteUrl ?>" class="botao selected">Recuperar</a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="botao selected" href="<?php echo $entity->destroyUrl; ?>">Excluir Definitivamente</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
