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
                <?= $this->text('title', i::__('Aqui você encontra as informações da cultura de sua região!')) ?>
            </label>
            <label class="description">
                <?= $this->text('description', i::__('Mas para isso, precisamos da sua ajuda!!! Faça parte você também: cadastre seus projetos, espaços e eventos.')) ?>
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
                        <img src="<?php $this->asset($app->config['module.home']['home-opportunities']) ?>" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('opportunities', i::__('Faça a sua inscrição ou acesse o resultado de diversas convocatórias como editais, oficinas, prêmios e concursos. Você também pode criar o seu próprio formulário e divulgar uma oportunidade para outros agentes culturais.')) ?></p>
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
                        <img src="<?php $this->asset($app->config['module.home']['home-events']) ?>" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('events', i::__('Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente. (Mais uma linha aqui pra fechar cinco linhas)')) ?></p>
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
                        <img src="<?php $this->asset($app->config['module.home']['home-spaces']) ?>" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('spaces', i::__('Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.')) ?></p>
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
                        <img src="<?php $this->asset($app->config['module.home']['home-agents']) ?>" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('agents', i::__('Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural da região. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), (...)')) ?></p>
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
                        <img src="<?php $this->asset($app->config['module.home']['home-projects']) ?>" />
                    </div>
                </div>
                <div class="card__right">
                    <p><?= $this->text('projects', i::__('Aqui você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma.')) ?></p>
                    <mc-link route="search/projects" class="button button--icon button--sm project__color">
                        <?= i::__('Ver todos')?>
                        <mc-icon name="access"></mc-icon>
                    </mc-link>
                </div>
            </div>
        </div>
    </div>
</div>
