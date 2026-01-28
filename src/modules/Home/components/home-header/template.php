<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    home-search
');

?>
<div :class="['home-header', {'home-header--withBanner' : banner}] ">
    <div class="home-header__content">
        <?php  $this->applyTemplateHook('home-header-content', 'before') ?>

        <div class="home-header__main">
            <label class="home-header__title">
                <?= $this->text('title', i::__('Boas vindas ao Mapa Cultural')) ?>
            </label>
            <p class="home-header__description">
                <?= $this->text('description', i::__('O Mapas Culturais é uma ferramenta de gestão cultural, que garante a estruturação de Sistemas de Informações e Indicadores. A plataforma oferece soluções para o mapeamento colaborativo de agentes culturais, realização de todas as etapas de editais e fomentos, organização de uma agenda cultural e divulgação espaços culturais dos territórios.')) ?>
            </p>
        </div>

        <div v-if="banner || secondBanner" class="home-header__banners">
            <?php $this->part('first-banner'); ?>

            <?php $this->part('second-banner'); ?>

            <?php $this->part('third-banner'); ?>
        </div>
  
        <?php  $this->applyTemplateHook('home-header-content', 'after') ?>
    </div>
    <div class="home-header__background">
        <div class="img">
            <img :src="background" />
        </div>
    </div>
    <!-- <home-search></home-search> -->
</div>