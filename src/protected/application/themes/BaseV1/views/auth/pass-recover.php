<br/>

<h2>Recuperar senha</h2>

<?php if($login_error) echo $login_error_msg; ?>

<form action="<?php echo $form_action; ?>" method="POST">

    
    Email
    <input type="text" name="email" value="<?php echo $triedEmail; ?>" />
    <br/><br/>
    Senha
    <input type="passowrd" name="password" value="" />

    <input type="submit" value="Recuperar" />

</form>

