<br/>

<h2>Login</h2>

<?php if($login_error) echo $login_error_msg; ?>

<form action="<?php echo $login_form_action; ?>" method="POST">

    
    Email
    <input type="text" name="email" value="<?php echo $triedEmail; ?>" />
    <br/><br/>
    Senha
    <input type="passowrd" name="password" value="" />

    <input type="submit" value="Entrar" />

</form>

<h2>Register</h2>

<?php if($register_error) echo $register_error_msg; ?>

<form action="<?php echo $register_form_action; ?>" method="POST">

    Nome
    <input type="text" name="name" value="<?php echo $triedName; ?>" />
    <br/><br/>
    Email
    <input type="text" name="email" value="" />
    <br/><br/>
    Senha
    <input type="passowrd" name="password" value="" />

    <input type="submit" value="Cadastrar" />

</form>

<h2>Redes sociais</h2>


<p>Utilice su cuenta en otros servicios para autenticarse (*):</p>
<a href="<?php echo $app->createUrl('auth', 'facebook') ?>"><img src="<?php $this->asset('img/fb-login.png'); ?>" /></a>&nbsp;&nbsp;
<a href="<?php echo $app->createUrl('auth', 'google') ?>"><img src="<?php $this->asset('img/go-login.png'); ?>" /></a>&nbsp;&nbsp;
<a href="<?php echo $app->createUrl('auth', 'linkedin') ?>"><img src="<?php $this->asset('img/ln-login.png'); ?>" /></a>
<!--<a href="<?php echo $app->createUrl('auth', 'twitter') ?>">Twitter</a> -->

<br/>
<br/>
<font size=3>(*) Sus datos personales (nombre, email)  son almacenados amparados en la Ley 18.831 de protección de datos personales</font>
<br/>
