<?php
usort($users, function($a, $b){
    $prof1 = $a->profile;
    $prof2 = $b->profile;
    
    if(!$prof1 || !$prof2 || strtolower($prof1->name) == strtolower($prof2->name))
        return 0;
    if(strtolower($prof1->name) > strtolower($prof2->name))
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

<form method="POST" action="<?php echo $new_user_form_action ?>">
    <h2>Criar novo usuário</h2>
    <p><label> Name: <input type="text" name="name" value="" /></label></p>
    <p><label> E-mail: <input type="email" name="email" value="" /></label></p>
    <input type="submit" value="Criar"/>
</form>