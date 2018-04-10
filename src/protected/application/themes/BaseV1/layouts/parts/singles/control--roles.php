<div id="funcao-do-agente" class="dropdown dropdown-select">
    <div class="placeholder js-selected">
        
        <?php if ($entity->user->is('saasSuperAdmin')): ?>
            <span data-role="superAdmin"><?php echo $app->getRoleName('saasSuperAdmin'); ?></span>
        <?php elseif ($entity->user->is('saasAdmin')): ?>
            <span data-role="superAdmin"><?php echo $app->getRoleName('saasAdmin'); ?></span>
        <?php elseif ($entity->user->is('subiteAdmin')): ?>
            <span data-role="subsiteAdmin"><?php echo $app->getRoleName('subsiteAdmin'); ?></span>
        <?php elseif ($entity->user->is('superAdmin')): ?>
            <span data-role="superAdmin"><?php echo $app->getRoleName('superAdmin'); ?></span>
        <?php elseif ($entity->user->is('admin')): ?>
            <span data-role="admin"><?php echo $app->getRoleName('admin'); ?></span>
        <?php else: ?>
            <span><?php \MapasCulturais\i::_e("Normal");?></span>
        <?php endif; ?>
    </div>
    <div class="submenu-dropdown js-options">
        <ul>
            <li>
                <span><?php \MapasCulturais\i::_e("Normal");?></span>
            </li>
            
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

            <?php if ($entity->user->canUser('addRoleSubsiteAdmin')): ?>
                <li data-role="subsiteAdmin">
                    <span><?php echo $app->getRoleName('subsiteAdmin') ?></span>
                </li>
            <?php endif; ?>
                
            <?php if ($entity->user->canUser('addRoleSaasAdmin')): ?>
                <li data-role="saasAdmin">
                    <span><?php echo $app->getRoleName('saasAdmin') ?></span>
                </li>
            <?php endif; ?>
            
            <?php if ($entity->user->canUser('addRoleSaasSuperAdmin')): ?>
                <li data-role="saasSuperAdmin">
                    <span><?php echo $app->getRoleName('saasSuperAdmin') ?></span>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</div>