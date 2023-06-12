<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    home-search
');

$this->tex
?>
<div class="home-header">
    <div class="home-header__content">
        <div class="home-header__content--left">
            <div class="content">
                <label class="content__title">
                    <?= $this->text('title', i::__('Bem-vinde ao Mapas Culturais')) ?>
                </label>
                <p class="content__description">
                    <?= $this->text('description', i::__('O Mapas Culturais é uma ferramenta de gestão cultural, que garante a estruturação de Sistemas de Informações e Indicadores. A plataforma oferece soluções para o mapeamento colaborativo de agentes culturais, realização de todas as etapas de editais e fomentos, organização de uma agenda cultural e divulgação espaços culturais dos territórios.')) ?>
                </p>
            </div>
        </div>

        <div class="home-header__content--right">
            <div class="img">
                <img src="<?php $this->asset('img/home/home-header/home-header2.jpg') ?>" />
            </div>
        </div>
    </div>
    <!-- <home-search></home-search> -->
</div>