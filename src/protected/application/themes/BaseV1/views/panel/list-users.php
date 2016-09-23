<?php
$this->layout = 'panel';
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Usuários e papéis</h2>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#superadmin">Super Administradores</a></li>
        <li><a href="#admin">Administradores</a></li>
        <li><a href="#staff">Membros da equipe</a></li>
    </ul>
    <div id="superadmin">
        <?php foreach($superadmins as $u): ?>
            Email: <?php echo $u->email; ?>
            <?php if ($u->canUser('RemoveRoleSuperAdmin')): ?>
                (remover)
            <?php endif; ?>
            
        <?php endforeach; ?>
        <?php if(!$superadmins): ?>
            <div class="alert info">Não há superadministradores</div>
        <?php endif; ?>
    </div>
    <div id="admin">
        <?php foreach($admins as $u): ?>
            Email: <?php echo $u->email; ?>
            <?php if ($u->canUser('RemoveRoleAdmin')): ?>
                (remover)
            <?php endif; ?>
            
        <?php endforeach; ?>
        <?php if(!$admins): ?>
            <div class="alert info">Não há administradores</div>
        <?php endif; ?>
    </div>

    <div id="staff">
        <?php foreach($staff as $u): ?>
            Email: <?php echo $u->profile->name; ?>
            <?php if ($u->canUser('RemoveRole')): ?>
                <a class="js-confirm-before-go" data-confirm-text="Você tem certeza que deseja remover este usuário da listade membros da equipe?" href="<?php echo $app->createUrl('agent', 'removeRole', ['id' => $u->profile->id, 'role' => 'staff']); ?>">
                (remover)
                </a>
            <?php endif; ?>
            
        <?php endforeach; ?>
        <?php if(!$staff): ?>
            <div class="alert info">Não há administradores</div>
        <?php endif; ?>
    </div>

</div>
