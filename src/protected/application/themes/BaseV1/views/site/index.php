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

$agent_img_attributes = $space_img_attributes = $event_img_attributes = $project_img_attributes = 'class="random-feature no-image"';

$agent = $this->getOneVerifiedEntity($class_agent);
if($agent && $img_url = $this->getEntityFeaturedImageUrl($agent)){
    $agent_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$space = $this->getOneVerifiedEntity($class_space);
if($space && $img_url = $this->getEntityFeaturedImageUrl($space)){
    $space_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$event = $this->getOneVerifiedEntity($class_event);
if($event && $img_url = $this->getEntityFeaturedImageUrl($event)){
    $event_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$project = $this->getOneVerifiedEntity($class_project);
if($project && $img_url = $this->getEntityFeaturedImageUrl($project)){
    $project_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_agents = $this->searchAgentsUrl;
$url_search_spaces = $this->searchSpacesUrl;
$url_search_events = $this->searchEventsUrl;
$url_search_projects = $this->searchProjectsUrl;

?>
<section id="home-watermark">

</section>
<section id="home-intro" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><?php $this->dict('home: title') ?></h1>
        <p><?php $this->dict('home: welcome') ?></p>
        <form id="home-search-form" class="clearfix" ng-non-bindable>
            <input tabindex="1" id="campo-de-busca" class="search-field" type="text" name="campo-de-busca" placeholder="Digite uma palavra-chave"/>
            <div id="home-search-filter" class="dropdown" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}}),{{entity}}:(keyword:'{{keyword}}'))">
                <div class="placeholder"><span class="icon icon-search"></span> Buscar</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li tabindex="2" id="events-filter"  data-entity="event"><span class="icon icon-event"></span> Eventos</li>
                        <li tabindex="3" id="agents-filter"  data-entity="agent"><span class="icon icon-agent"></span> Agentes</li>
                        <li tabindex="4" id="spaces-filter"  data-entity="space"><span class="icon icon-space"></span> Espaços</li>
                        <li tabindex="5" id="projects-filter" data-entity="project" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}},viewMode:list),{{entity}}:(keyword:'{{keyword}}'))"><span class="icon icon-project"></span> Projetos</li>
                    </ul>
                </div>
            </div>
        </form>
        <a class="btn btn-accent btn-large" href="<?php echo $app->createUrl('panel') ?>"><?php $this->dict('home: colabore') ?></a>
    </div>
    <div class="view-more"><a class="hltip icon icon-select-arrow" href="#home-events" title="Saiba mais"></a></div>
</section>

<article id="home-events" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-event"></span> Eventos</h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_events ?></div>
                <div class="statistic-label">eventos agendados</div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_events ?></div>
                <div class="statistic-label">eventos da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: events') ?></p>
        <h4>Encontre eventos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#event-terms">Linguagem</a></li>
        </ul>
        <div id="event-terms" class="tag-box">
            <div>
                <?php foreach ($event_linguagens as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="box">
        <?php if($event): ?>
        <a href="<?php echo $event->singleUrl ?>">
            <div <?php echo $event_img_attributes;?>>
                <div class="feature-content">
                    <h3>destaque</h3>
                    <h2><?php echo $event->name ?></h2>
                    <p><?php echo $event->shortDescription ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('event', 'create') ?>">Adicionar evento</a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_events ?>">Ver tudo</a>
    </div>
</article>


<article id="home-agents" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-agent"></span> Agentes</h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_agents ?></div>
                <div class="statistic-label">agentes cadastrados</div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_agents ?></div>
                <div class="statistic-label">agentes da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: agents') ?></p>
        <h4>Encontre agentes por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#agent-terms">Área de atuação</a></li>
            <li><a href="#agent-types">Tipo</a></li>
        </ul>
        <div id="agent-terms" class="tag-box">
            <div>
                <?php foreach ($agent_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(areas:!(<?php echo $i ?>)),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="agent-types" class="tag-box">
            <div>
                <?php foreach ($agent_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(type:<?php echo $t->id ?>),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="box">
        <?php if($agent): ?>
        <a href="<?php echo $agent->singleUrl ?>">
            <div <?php echo $agent_img_attributes;?>>
                <div class="feature-content">
                    <h3>destaque</h3>
                    <h2><?php echo $agent->name ?></h2>
                    <p><?php echo $agent->shortDescription ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('agent', 'create') ?>">Adicionar agente</a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_agents ?>">Ver tudo</a>
    </div>
</article>


<article id="home-spaces" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-space"></span> Espaços</h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_spaces ?></div>
                <div class="statistic-label">espaços cadastrados</div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_spaces; ?></div>
                <div class="statistic-label">espaços da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: spaces'); ?></p>
        <h4>Encontre espaços por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#space-terms">Área de atuação</a></li>
            <li><a href="#space-types">Tipo</a></li>
        </ul>
        <div id="space-terms" class="tag-box">
            <div>
                <?php foreach ($space_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(areas:!(<?php echo $i ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="space-types" class="tag-box">
            <div>
                <?php foreach ($space_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(types:!(<?php echo $t->id ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <div class="box">
        <?php if($space): ?>
            <a href="<?php echo $space->singleUrl ?>">
                <div <?php echo $space_img_attributes;?>>
                    <div class="feature-content">
                        <h3>destaque</h3>
                        <h2><?php echo $space->name ?></h2>
                        <p><?php echo $space->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('space', 'create') ?>">Adicionar espaço</a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_spaces ?>">Ver tudo</a>
    </div>
</article>


<article id="home-projects" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-project"></span> Projetos</h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_projects; ?></div>
                <div class="statistic-label">projetos cadastrados</div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_projects; ?></div>
                <div class="statistic-label">projetos da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: projects') ?></p>
        <h4>Encontre projetos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#project-types">Tipo</a></li>
        </ul>
        <div id="project-types"  class="tag-box">
            <div>
                <?php foreach ($project_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(types:!(<?php echo $t->id ?>)),global:(enabled:(project:!t),filterEntity:project,viewMode:list))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="box">
        <?php if($project): ?>
            <a href="<?php echo $project->singleUrl ?>">
                <div <?php echo $project_img_attributes;?>>
                    <div class="feature-content">
                        <h3>destaque</h3>
                        <h2><?php echo $project->name ?></h2>
                        <p><?php echo $project->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('project', 'create') ?>">Adicionar projeto</a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_projects ?>">Ver tudo</a>
    </div>
</article>
<article id="home-developers" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-developers"></span> Desenvolvedores</h1>
        <p><?php $this->dict('home: home_devs'); ?> </p>
    </div>
</article>
<nav id="home-nav">
    <ul>
        <li><a class="up icon icon-arrow-up" href="#"></a></li>
        <li id="nav-intro">
            <a class="icon icon-home" href="#home-intro"></a>
            <span class="nav-title">Introdução</span>
        </li>
        <li id="nav-events">
            <a class="icon icon-event" href="#home-events"></a>
            <span class="nav-title">Eventos</span>
        </li>
        <li id="nav-agents">
            <a class="icon icon-agent" href="#home-agents"></a>
            <span class="nav-title">Agentes</span>
        </li>
        <li id="nav-spaces">
            <a class="icon icon-space" href="#home-spaces"></a>
            <span class="nav-title">Espaços</span>
        </li>
        <li id="nav-projects">
            <a class="icon icon-project" href="#home-projects"></a>
            <span class="nav-title">Projetos</span>
        </li>
        <li id="nav-developers">
            <a class="icon icon-developers" href="#home-developers"></a>
            <span class="nav-title">Desenvolvedores</span>
        </li>
        <li><a class="down icon icon-select-arrow" href="#"></a></li>
    </ul>
</nav>
