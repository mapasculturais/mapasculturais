<h1><?php echo \MapasCulturais\i::__('Fake Authentication'); ?></h1>
<form method="GET" action="<?php echo $form_action ?>">
    <?php echo \MapasCulturais\i::__('Login with user') ?>:
    <select name="fake_authentication_user_id">
        <?php foreach($users as $u): if(!$u['profile']) continue;
            $role =  $u['roles']; ?>
        <option value="<?php echo $u['id'] ?>"><?php echo "{$u['profile']} ({$u['id']}) = {$role}"; ?></option>
        <?php endforeach; ?>
    </select>
    <br/>
    <input type="submit" value="ok" />
</form>

<form method="POST" action="<?php echo $new_user_form_action ?>">
    <h2>Criar novo usuário</h2>
    <p><label> Name: <input type="text" name="name" value="" /></label></p>
    <p><label> E-mail: <input type="email" name="email" value="" /></label></p>
    <input type="submit" value="Criar"/>
</form>
