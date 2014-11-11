<?php
$this->jsObject['spinner'] = $this->asset('img/spinner_192.gif', false);

$app = \MapasCulturais\App::i();
$em = $app->em;

$class_event = 'MapasCulturais\Entities\Event';
$class_agent = 'MapasCulturais\Entities\Agent';
$class_space = 'MapasCulturais\Entities\Space';
$class_project = 'MapasCulturais\Entities\Project';

$class_file = 'MapasCulturais\Entities\File';

$num_events             = $this->getNumEvents();
$num_verified_events    = $this->getNumVerifiedEvents();
$num_agents             = $this->getNumEntities($class_agent);
$num_verified_agents    = $this->getNumEntities($class_agent, true);
$num_spaces             = $this->getNumEntities($class_space);
$num_verified_spaces    = $this->getNumEntities($class_space, true);
$num_projects           = $this->getNumEntities($class_project);
$num_verified_projects  = $this->getNumEntities($class_project, true);

$event_linguagens = array_values($app->getRegisteredTaxonomy($class_event, 'linguagem')->restrictedTerms);
$agent_areas = array_values($app->getRegisteredTaxonomy($class_agent, 'area')->restrictedTerms);
$space_areas = array_values($app->getRegisteredTaxonomy($class_space, 'area')->restrictedTerms);

sort($event_linguagens);
sort($agent_areas);
sort($space_areas);

$agent_types = $app->getRegisteredEntityTypes($class_agent);
$space_types = $app->getRegisteredEntityTypes($class_space);
$project_types = $app->getRegisteredEntityTypes($class_project);

$agent = $this->getOneVerifiedEntityWithImages($class_agent);
if($agent) $agent_img_url = $this->getEntityFeaturedImageUrl($agent);

$space = $this->getOneVerifiedEntityWithImages($class_space);
if($space) $space_img_url = $this->getEntityFeaturedImageUrl($space);

$event = $this->getOneVerifiedEntityWithImages($class_event);
if($event) $event_img_url = $this->getEntityFeaturedImageUrl($event);

$project = $this->getOneVerifiedEntityWithImages($class_project);
if($project) $project_img_url = $this->getEntityFeaturedImageUrl($project);


$url_search_agents = $this->searchAgentsUrl;
$url_search_spaces = $this->searchSpacesUrl;
$url_search_events = $this->searchEventsUrl;
$url_search_projects = $this->searchProjectsUrl;

?>
<section id="capa-marca-dagua">

</section>
<section id="capa-intro" class="js-page-menu-item objeto-capa clearfix fundo-laranja">
    <div class="box">
        <h1>Bem-vind@!</h1>
        <p><?php $this->dict('home: welcome') ?></p>
        <form id="form-de-busca-geral" class="clearfix" ng-non-bindable>
            <input tabindex="1" id="campo-de-busca" class="campo-de-busca" type="text" name="campo-de-busca" placeholder="Digite uma palavra-chave"/>
            <div id="filtro-da-capa" class="dropdown" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}}),{{entity}}:(keyword:'{{keyword}}'))">
                <div class="placeholder"><span class="icone icon_search"></span> Buscar</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li tabindex="2" id="filtro-de-eventos"  data-entity="event"><span class="icone icon_calendar"></span> Eventos</li>
                        <li tabindex="3" id="filtro-de-agentes"  data-entity="agent"><span class="icone icon_profile"></span> Agentes</li>
                        <li tabindex="4" id="filtro-de-espacos"  data-entity="space"><span class="icone icon_building"></span> Espaços</li>
                        <li tabindex="5" id="filtro-de-projetos" data-entity="project" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}},viewMode:list),{{entity}}:(keyword:'{{keyword}}'))"><span class="icone icon_document_alt"></span> Projetos</li>
                    </ul>
                </div>
            </div>
        </form>
        <p class="textcenter"><a class="botao-grande" href="<?php echo $app->createUrl('panel') ?>"><?php $this->dict('home: colabore') ?></a></p>
    </div>
    <div class="ver-mais"><a class="hltip icone arrow_carrot-down" href="#capa-eventos" title="Saiba mais"></a></div>
</section>

<article id="capa-eventos" class="js-page-menu-item objeto-capa clearfix fundo-verde">
    <div class="box">
        <h1><span class="icone icon_calendar"></span> Eventos</h1>
        <div class="clearfix">
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_events ?></div>
                <div class="label-das-estatisticas">eventos agendados</div>
            </div>
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_verified_events ?></div>
                <div class="label-das-estatisticas">eventos da SMC</div>
            </div>
        </div>
        <p><?php $this->dict('home: events') ?></p>
        <h4>Encontre eventos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#event-terms">Linguagem</a></li>
        </ul>
        <div id="event-terms" class="tag-box">
            <?php foreach ($event_linguagens as $i => $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="box">
        <?php if($event): ?>
        <a href="<?php echo $event->singleUrl ?>">
            <div class="destaque-aleatorio">
                <div class="destaque-content">
                    <h3>destaque</h3>
                    <h2><?php echo $event->name ?></h2>
                    <p><?php echo $event->shortDescription ?></p>
                </div>
                <img src="<?php echo $event_img_url ?>" />
            </div>
        </a>
        <?php endif; ?>
        <a class="botao-grande" href="<?php echo $url_search_events ?>">Ver Todos Eventos da Semana</a>
        <a class="botao-grande adicionar" href="<?php echo $app->createUrl('event', 'create') ?>">Adicionar Eventos</a>
    </div>
</article>


<article id="capa-agentes" class="js-page-menu-item objeto-capa clearfix fundo-azul">
    <div class="box">
        <h1><span class="icone icon_profile"></span> Agentes</h1>
        <div class="clearfix">
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_agents ?></div>
                <div class="label-das-estatisticas">agentes cadastrados</div>
            </div>
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_verified_agents ?></div>
                <div class="label-das-estatisticas">agentes da SMC</div>
            </div>
        </div>
        <p><?php $this->dict('home: agents') ?></p>
        <h4>Encontre agentes por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#agent-terms">Área de atuação</a></li>
            <li><a href="#agent-types">Tipo</a></li>
        </ul>
        <div id="agent-terms" class="tag-box">
            <?php foreach ($agent_areas as $i => $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(areas:!(<?php echo $i ?>)),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t ?></a>
            <?php endforeach; ?>
        </div>
        <div id="agent-types" class="tag-box">
            <?php foreach ($agent_types as $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(type:<?php echo $t->id ?>),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t->name ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="box">
        <?php if($agent): ?>
        <a href="<?php echo $agent->singleUrl ?>">
            <div class="destaque-aleatorio">
                <div class="destaque-content">
                    <h3>destaque</h3>
                    <h2><?php echo $agent->name ?></h2>
                    <p><?php echo $agent->shortDescription ?></p>
                </div>
                <img src="<?php echo $agent_img_url ?>" />
            </div>
        </a>
        <?php endif; ?>
        <a class="botao-grande" href="<?php echo $url_search_agents ?>">Ver Todos Agentes</a>
        <a class="botao-grande adicionar" href="<?php echo $app->createUrl('agent', 'create') ?>">Adicionar Agentes</a>
    </div>
</article>


<article id="capa-espacos" class="js-page-menu-item objeto-capa clearfix fundo-rosa">
    <div class="box">
        <h1><span class="icone icon_building"></span> Espaços</h1>
        <div class="clearfix">
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_spaces ?></div>
                <div class="label-das-estatisticas">espaços cadastrados</div>
            </div>
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_verified_spaces; ?></div>
                <div class="label-das-estatisticas">espaços da SMC</div>
            </div>
        </div>
        <p><?php $this->dict('home: spaces'); ?></p>
        <h4>Encontre espaços por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#space-terms">Área de atuação</a></li>
            <li><a href="#space-types">Tipo</a></li>
        </ul>
        <div id="space-terms" class="tag-box">
            <?php foreach ($space_areas as $i => $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(areas:!(<?php echo $i ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t ?></a>
            <?php endforeach; ?>
        </div>
        <div id="space-types" class="tag-box">
            <?php foreach ($space_types as $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(types:!(<?php echo $t->id ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t->name ?></a>
            <?php endforeach; ?>
        </div>

    </div>
    <div class="box">
        <?php if($space): ?>
            <a href="<?php echo $space->singleUrl ?>">
                <div class="destaque-aleatorio">
                    <div class="destaque-content">
                        <h3>destaque</h3>
                        <h2><?php echo $space->name ?></h2>
                        <p><?php echo $space->shortDescription ?></p>
                    </div>
                    <img src="<?php echo $space_img_url ?>" />
                </div>
            </a>
        <?php endif; ?>
        <a class="botao-grande" href="<?php echo $url_search_spaces ?>">Ver Todos Espaços</a>
        <a class="botao-grande adicionar" href="<?php echo $app->createUrl('space', 'create') ?>">Adicionar Espaços</a>
    </div>
</article>


<article id="capa-projetos" class="js-page-menu-item objeto-capa clearfix fundo-vermelho">
    <div class="box">
        <h1><span class="icone icon_document_alt"></span> Projetos</h1>
        <div class="clearfix">
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_projects; ?></div>
                <div class="label-das-estatisticas">projetos cadastrados</div>
            </div>
            <div class="estatisticas">
                <div class="estatistica"><?php echo $num_verified_projects; ?></div>
                <div class="label-das-estatisticas">projetos da SMC</div>
            </div>
        </div>
        <p><?php $this->dict('home: projects') ?></p>
        <h4>Encontre projetos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#project-types">Tipo</a></li>
        </ul>
        <div id="project-types"  class="tag-box">
            <?php foreach ($project_types as $t): ?>
                <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(types:!(<?php echo $t->id ?>)),global:(enabled:(project:!t),filterEntity:project,viewMode:list))"><?php echo $t->name ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="box">
        <?php if($project): ?>
            <a href="<?php echo $project->singleUrl ?>">
                <div class="destaque-aleatorio">
                    <div class="destaque-content">
                        <h3>destaque</h3>
                        <h2><?php echo $project->name ?></h2>
                        <p><?php echo $project->shortDescription ?></p>
                    </div>
                    <img src="<?php echo $project_img_url ?>" />
                </div>
            </a>
        <?php endif; ?>
        <a class="botao-grande" href="<?php echo $url_search_projects ?>">Ver Todos Projetos</a>
        <a class="botao-grande adicionar" href="<?php echo $app->createUrl('project', 'create') ?>">Adicionar Projetos</a>
    </div>
</article>
<article id="capa-desenvolvedores" class="js-page-menu-item objeto-capa clearfix fundo-roxo">
    <div class="box">
        <h1><span class="icone icon_tools"></span> Desenvolvedores</h1>
        <p>
             Existem algumas maneiras de desenvolvedores interagirem com o SP Cultura. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o SP Cultura é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.
        </p>
    </div>
</article>
<nav id="capa-nav">
    <ul>
        <li><a class="up icone arrow_carrot-up" href="#"></a></li>
        <li id="nav-intro">
            <a class="icone icon_house" href="#capa-intro"></a>
            <span class="nav-title">Introdução</span>
        </li>
        <li id="nav-eventos">
            <a class="icone icon_calendar" href="#capa-eventos"></a>
            <span class="nav-title">Eventos</span>
        </li>
        <li id="nav-agentes">
            <a class="icone icon_profile" href="#capa-agentes"></a>
            <span class="nav-title">Agentes</span>
        </li>
        <li id="nav-espacos">
            <a class="icone icon_building" href="#capa-espacos"></a>
            <span class="nav-title">Espaços</span>
        </li>
        <li id="nav-projetos">
            <a class="icone icon_document_alt" href="#capa-projetos"></a>
            <span class="nav-title">Projetos</span>
        </li>
        <li id="nav-desenvolvedores">
            <a class="icone icon_tools" href="#capa-desenvolvedores"></a>
            <span class="nav-title">Desenvolvedores</span>
        </li>
        <li><a class="down icone arrow_carrot-down" href="#"></a></li>
    </ul>
</nav>
