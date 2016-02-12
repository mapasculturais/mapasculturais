<h1><?php echo $app->txt('Autenticación Fake'); ?></h1>
<form method="GET" action="<?php echo $form_action ?>">
    <?php echo $app->txt('Acceder con el usuario') ?>:
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
    <h2>Crear nuevo usuario</h2>
    <p><label> Nombre: <input type="text" name="name" value="" /></label></p>
    <p><label> E-mail: <input type="email" name="email" value="" /></label></p>
    <input type="submit" value="Crear"/>
</form>