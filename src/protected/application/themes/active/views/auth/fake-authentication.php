<h1><?php echo $app->txt('Fake Authentication'); ?></h1>
<form method="GET" action="<?php echo $form_action ?>">
    <?php echo $app->txt('Login with user') ?>:
    <select name="auth_dev_user_id">
        <?php foreach($users as $u): ?>
        <option value="<?php echo $u->id ?>"><?php echo "($u->id) $u->email"; ?></option>
        <?php endforeach; ?>
    </select>
    <br/>
    <input type="submit" value="ok" />
</form>