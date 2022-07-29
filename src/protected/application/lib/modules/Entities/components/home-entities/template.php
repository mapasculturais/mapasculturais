<?php
use MapasCulturais\i;
?>
<div class="home-entities">
    
    <div class="home-entities__content">
        <div class="home-entities__content--header">
            <label class="title">
                <?php i::_e('O que você encontra em Mapas Culturais,') ?>
            </label>
            <label class="description">
                <p></p><?php i::_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla. ,') ?></p>
            </label>
        
        </div>
        <div class="home-entities__content--cards">
            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon opportunity__background">
                            <iconify icon="icons8:idea"></iconify>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Oportunidades') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>
                        Faça a sua inscrição ou acesse o resultado de diversas convocatórias como editais, oficinas, prêmios e concursos. Você também pode criar o seu próprio formulário e divulgar uma oportunidade para outros agentes culturais.
                    </p>
                    <a class="button button--icon button--sm opportunity__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>
        </div>
    </div>
</div>
