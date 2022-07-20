<?php
$roles = MapasCulturais\App::i()->getRoles();
$user_role = null;
foreach ($roles as $role) {
    if ($entity->user->is($role->role)) {
        $user_role = $role;
        break;
    }
}
?>
<div id="funcao-do-agente" class="dropdown dropdown-select">
    <div class="placeholder js-selected">
        <?php if ($user_role): ?>
            <span data-role="<?= $role->role ?>"><?= $role->name ?></span>
        <?php else: ?>
            <span><?php \MapasCulturais\i::_e("Normal");?></span>
        <?php endif; ?>
    </div>
    <div class="submenu-dropdown js-options">
        <ul>
            <li data-role="">
                <span><?php \MapasCulturais\i::_e("Normal");?></span>
            </li>
            <?php foreach($roles as $role): ?>
                <?php if ($role->canUserManageRole()): ?>
                    <li data-role="<?=$role->role?>">
                        <span><?=$role->name ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>