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

        <div class="home-header__main">
            <label class="home-header__title">
                <?= $this->text('title', i::__('Bem-vinde ao Mapas Culturais')) ?>
            </label>
            <p class="home-header__description">
                <?= $this->text('description', i::__('O Mapas Culturais é uma ferramenta de gestão cultural, que garante a estruturação de Sistemas de Informações e Indicadores. A plataforma oferece soluções para o mapeamento colaborativo de agentes culturais, realização de todas as etapas de editais e fomentos, organização de uma agenda cultural e divulgação espaços culturais dos territórios.')) ?>
            </p>
        </div>

        <div v-if="banner" class="home-header__banner">
            <a v-if="bannerLink" :href="bannerLink" :download="downloadableLink ? 'download' : null" :target="!downloadableLink ? '_blank' : null">
                <img :src="banner" />
            </a>
            <img v-if="!bannerLink" :src="banner" />
        </div>
  
    </div>
    <div class="home-header__background">
        <div class="img">
            <img :src="background" />
        </div>
    </div>
    <!-- <home-search></home-search> -->
</div>