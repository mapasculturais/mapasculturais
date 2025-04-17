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
        <label class="home-developers__content--title"><?= $this->text('title',i::__('Alô desenvolvedores')) ?></label>
        <div class="home-developers__content--description">
            <?= $this->text('description',i::__('O Mapas Culturais é um software livre de gestão do fomento à cultura e mapeamento colaborativo. Ele foi criado de forma colaborativa pela hacklab/, órgãos públicos de cultura, organizações não governamentais, empresas e coletivos que acreditam na plataforma. Você pode contribuir para o seu desenvolvimento através do GitHub.')) ?>
        </div>
        <div class="home-developers__content--link">
            <a class="link" href="https://github.com/culturagovbr/mapadacultura"> 
                <?php i::_e("Conheça o repositório") ?>
                <mc-icon name="github"></mc-icon>
            </a>
        </div>
    </div>
</div>