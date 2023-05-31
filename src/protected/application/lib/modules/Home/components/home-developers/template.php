<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>
<div class="home-developers"> 
    <div class="home-developers__content">
        <span class="dev-icon"><mc-icon name="code"></mc-icon></span>
        <label class="home-developers__content--title"><?php i::_e('Alô desenvolvedores,') ?></label>
        <div class="home-developers__content--description">
            <?php i::_e('Além disso, Mapas Culturais é um software livre, criado em parceria entre a hacklab/, secretarias de cultura, organizações não governamentais, empresas e coletivos que investem na plataforma. Você pode contribuir para o seu desenvolvimento através do GitHub.') ?>
        </div>
        <div class="home-developers__content--link">
            <a class="link" href="https://github.com/mapasculturais"> 
                <?php i::_e("Conheça o repositório") ?>
                <mc-icon name="github"></mc-icon>
            </a>
        </div>
    </div>
</div>