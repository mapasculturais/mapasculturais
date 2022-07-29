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
                        {{opportunityText}}
                    </p>
                    <a class="button button--icon button--sm opportunity__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon event__background">
                            <iconify icon="ant-design:calendar-twotone"></iconify>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Eventos') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>
                        {{eventText}}
                    </p>
                    <a class="button button--icon button--sm event__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon space__background">
                            <iconify icon="clarity:building-line"></iconify>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Espaços') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>
                        {{spaceText}}
                    </p>
                    <a class="button button--icon button--sm space__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon agent__background">
                            <iconify icon="fa-solid:user-friends"></iconify>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Agentes') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>
                        {{agentText}}
                    </p>
                    <a class="button button--icon button--sm agent__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon project__background">
                            <iconify icon="ri:file-list-2-line"></iconify>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Projetos') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/mapa.jpg') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>
                        {{projectText}}
                    </p>
                    <a class="button button--icon button--sm project__color"><?php i::_e('Ver todos')?><iconify icon="ooui:previous-rtl"></iconify> </a>
                </div>

            </div>
        </div>
    </div>
</div>
