<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
');
?>
<div class="home-entities">
    
    <div class="home-entities__content">
        <div class="home-entities__content--header">
            <label class="title">
                <?= $this->text('title', i::__('Aqui você encontra informações de editais e oportunidades do Ministério da Cultura.')) ?>
            </label>
            <label class="description">
                <?= $this->text('description', i::__('Você também pode cadastrar seus projetos, espaços e eventos, e contribuir para o mapeamento cultural brasileiro.')) ?>
            </label>
        </div>
        
        <div class="home-entities__content--cards">
            <div v-if="global.enabledEntities.opportunities" class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon opportunity__background">
                            <mc-icon name="opportunity"></mc-icon>
                        </div>                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?= i::__('Oportunidades') ?>
                            </label>
                        </div>
                    </div>
                    <div class="card__left--img">
                        <img :src="subsite?.files?.opportunityBanner ? subsite?.files?.opportunityBanner?.url : '<?php $this->asset($app->config['module.home']['home-opportunities']) ?>'" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('opportunities', i::__('Aqui você pode fazer sua inscrição nos editais e oportunidades do Ministério da Cultura (Minc), bem como acompanhar as inscrições em andamento. Nesse espaço, você também pode acessar outras oportunidades da cultura; tais como, oficinas, prêmios e concursos; criar uma oportunidade e divulgá-la para outros agentes culturais.')) ?></p>
                    <mc-link route="search/opportunities" class="button button--icon button--sm opportunity__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div v-if="global.enabledEntities.events" class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon event__background">
                            <mc-icon name="event"></mc-icon>
                        </div>                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?= i::__('Eventos') ?>
                            </label>
                        </div>
                    </div>
                    <div class="card__left--img">
                        <img :src="subsite?.files?.eventBanner ? subsite?.files?.eventBanner?.url : '<?php $this->asset($app->config['module.home']['home-events']) ?>'" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('eventText', i::__('Você pode pesquisar eventos culturais cadastrados na plataforma filtrando por região, área da cultura, etc. Você também pode incluir seus eventos culturais na plataforma e divulgá-los gratuitamente.')) ?></p>
                    <mc-link route="search/events" class="button button--icon button--sm event__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div v-if="global.enabledEntities.spaces" class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon space__background">
                            <mc-icon name="space"></mc-icon>
                        </div>                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?= i::__('Espaços') ?>
                            </label>
                        </div>
                    </div>
                    <div class="card__left--img">
                        <img :src="subsite?.files?.spaceBanner ? subsite?.files?.spaceBanner?.url : '<?php $this->asset($app->config['module.home']['home-spaces']) ?>'" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('spaces', i::__('Aqui você pode cadastrar seus espaços culturais e colaborar com o Mapa da Cultura! Além disso, você pode pesquisar por espaços culturais cadastrados na sua região; tais como teatros, bibliotecas, centros culturais e outros.')) ?></p>
                    <mc-link route="search/spaces" class="button button--icon button--sm space__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div v-if="global.enabledEntities.agents" class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon agent__background">
                            <mc-icon name="agent-2"></mc-icon>
                        </div>                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?= i::__('Agentes') ?>
                            </label>
                        </div>
                    </div>
                    <div class="card__left--img">
                        <img :src="subsite?.files?.agentBanner ? subsite?.files?.agentBanner?.url : '<?php $this->asset($app->config['module.home']['home-agents']) ?>'" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('agents', i::__('Neste espaço, é possível buscar e conhecer os agentes culturais cadastrados no Mapa da Cultura. Explore a diversidade de artistas, produtores, grupos, coletivos, bandas, instituições, que fazem parte da cultura! Participe e seja protagonista da cultura brasileira!')) ?></p>
                    <mc-link route="search/agents" class="button button--icon button--sm agent__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>

            <div v-if="global.enabledEntities.projects" class="card">
                <div class="card__left">
                    <div class="card__left--content">
                        <div class="card__left--content-icon project__background">
                            <mc-icon name="project"></mc-icon>
                        </div>                        
                        <div class="card__left--content-title">
                            <label class="title">
                                <?= i::__('Projetos') ?>
                            </label>
                        </div>
                    </div>
                    <div class="card__left--img">
                        <img :src="subsite?.files?.projectBanner ? subsite?.files?.projectBanner?.url : '<?php $this->asset($app->config['module.home']['home-projects']) ?>'" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('projects', i::__('Aqui você encontra projetos culturais cadastrados pelos agentes culturais usuários da plataforma Mapa da Cultura.')) ?></p>
                    <mc-link route="search/projects" class="button button--icon button--sm project__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>
        </div>
    </div>
</div>
