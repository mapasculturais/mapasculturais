<?php
use MapasCulturais\i;
$this->import('
    mc-link
');
?>
<div class="home-entities">
    
    <div class="home-entities__content">
        <div class="home-entities__content--header">
            <label class="title">
                <?php i::_e('Aqui você encontra as informações da cultura de sua região!') ?>
            </label>
            <label class="description">
                <p>
                    <?php i::_e('Mas para isso, precisamos da sua ajuda!!! Faça parte você também: cadastre seus projetos, espaços e eventos.') ?>
                </p>
            </label>
        </div>
        
        <div class="home-entities__content--cards">
            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon opportunity__background">
                            <mc-icon name="opportunity"></mc-icon>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Oportunidades') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/home-entities/opportunities.png') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>{{opportunityText}}</p>
                    <mc-link route="search/opportunities" class="button button--icon button--sm opportunity__color">
                        <?php i::_e('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon event__background">
                            <mc-icon name="event"></mc-icon>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Eventos') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/home-entities/events.png') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>{{eventText}}</p>
                    <mc-link route="search/events" class="button button--icon button--sm event__color">
                        <?php i::_e('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon space__background">
                            <mc-icon name="space"></mc-icon>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Espaços') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/home-entities/spaces.png') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>{{spaceText}}</p>
                    <mc-link route="search/spaces" class="button button--icon button--sm space__color">
                        <?php i::_e('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>

            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon agent__background">
                            <mc-icon name="agent-2"></mc-icon>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Agentes') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/home-entities/agents.png') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>{{agentText}}</p>
                    <mc-link route="search/agents" class="button button--icon button--sm agent__color">
                        <?php i::_e('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon project__background">
                            <mc-icon name="project"></mc-icon>
                        </div>
                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?php i::_e('Projetos') ?>
                            </label>
                        </div>
                    </div>

                    <div class="card__left--img">
                        <img src="<?php $this->asset('img/home/home-entities/projects.png') ?>" />
                    </div>
                </div>

                <div class="card__right">
                    <p>{{projectText}}</p>
                    <mc-link route="search/projects" class="button button--icon button--sm project__color">
                        <?php i::_e('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>
        </div>
    </div>
</div>
