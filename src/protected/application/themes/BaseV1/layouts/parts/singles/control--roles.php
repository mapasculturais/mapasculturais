<div id="funcao-do-agente" class="dropdown dropdown-select">
    <div class="placeholder js-selected">
        <?php if ($entity->user->is('superAdmin')): ?>
            <span data-role="superAdmin"><?php echo $app->getRoleName('superAdmin'); ?></span>
        <?php elseif ($entity->user->is('admin')): ?>
            <span data-role="admin"><?php echo $app->getRoleName('admin'); ?></span>
        <?php elseif ($entity->user->is('staff')): ?>
            <span data-role="staff"><?php echo $app->getRoleName('staff'); ?></span>
        <?php else: ?>
            <span><?php \MapasCulturais\i::_e("Normal");?></span>
        <?php endif; ?>
    </div>
    <div class="submenu-dropdown js-options">
        <ul>
            <li>
                <span><?php \MapasCulturais\i::_e("Normal");?></span>
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