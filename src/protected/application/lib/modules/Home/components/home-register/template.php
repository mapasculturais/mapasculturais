<?php
use MapasCulturais\i;
?>
<div class="home-register">
    <div class="home-register__background">
        <img class="home-register__background--img" src="<?php $this->asset('img/home/home-register/background.png') ?>" />
        <div class="home-register__background--mask"></div>
    </div>
    <div class="home-register__content">
        <label class="home-register__content--title"><?php i::_e('Faça seu cadastro e colabore com o Mapas Culturais') ?></label>
        <p class="home-register__content--description"><?php i::_e('Colabore com a plataforma livre, colaborativa e interativa de mapeamento do cenário cultural e instrumento de governança digital no aprimoramento da gestão pública, dos mecanismos de participação e da democratização do acesso às políticas culturais promovidas pela Secretaria da Cultura.'); ?>
        </p>
        <button class="home-register__content--button button button--icon button--bg">
            <?php i::_e('Fazer Cadastro')?>
            <mc-icon name="access"></mc-icon>
        </button>
    </div>
</div>