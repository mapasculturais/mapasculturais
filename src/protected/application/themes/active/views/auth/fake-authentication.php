<?php
usort($users, function($a, $b){
    if(!$a->profile || !$b->profile || strtolower($a->profile->name) == strtolower($b->profile->name))
        return 0;
    if(strtolower($a->profile->name) > strtolower($b->profile->name))
        return 1;
    else return -1;
});
?>
<h1><?php echo $app->txt('Fake Authentication'); ?></h1>
<form method="GET" action="<?php echo $form_action ?>">
    <?php echo $app->txt('Login with user') ?>:
    <select name="fake_authentication_user_id">
        <?php foreach($users as $u): if(!$u->profile) continue;
            $role =  $u->roles && $u->roles->toArray() ? implode(', ',array_map(function($e){ return $e->name; }, $u->roles->toArray())) : 'Normal'; ?>
        <option value="<?php echo $u->id ?>"><?php echo "{$u->profile->name} ({$u->id}) = {$role}"; ?></option>
        <?php endforeach; ?>
    </select>
    <br/>
    <input type="submit" value="ok" />
</form>