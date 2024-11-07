<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="home-register">
    <div class="home-register__background">
        <img class="home-register__background--img" :src="subsite?.files?.signupBanner ? subsite?.files?.signupBanner?.url : '<?php $this->asset($app->config['module.home']['home-register']) ?>'" />
        <div class="home-register__background--mask"></div>
    </div>
    <div class="home-register__content">
        <label class="home-register__content--title"><?= $this->text('title', i::__('Faça seu cadastro e colabore com o Mapa da Cultura!')) ?></label>
        <p class="home-register__content--description"><?= $this->text('description', i::__('Participe dessa plataforma livre, colaborativa e interativa de mapeamento cultural brasileiro. Ao se cadastrar no Mapa da Cultura, você fará parte de uma rede de fazedores de cultura e ainda fortalecerá a gestão de cultura do Brasil.')); ?>
        </p>
        <a href="<?= $app->createUrl('autenticacao', 'register') ?>" class="home-register__content--button button button--icon button--bg">
            <?= i::__('Fazer Cadastro')?>
            <mc-icon name="access"></mc-icon>
        </a>
    </div>
</div>