<?php
$app = \MapasCulturais\App::i();
$em = $app->em;

$class_event = 'MapasCulturais\Entities\Event';
$class_agent = 'MapasCulturais\Entities\Agent';
$class_space = 'MapasCulturais\Entities\Space';
$class_project = 'MapasCulturais\Entities\Project';

$class_file = 'MapasCulturais\Entities\File';

$num_events = $em->createQuery("SELECT COUNT(e) FROM $class_event e")->getSingleScalarResult();
$num_verified_events = $em->createQuery("SELECT COUNT(e) FROM $class_event e WHERE e.isVerified = TRUE")->getSingleScalarResult();

$num_agents = $em->createQuery("SELECT COUNT(e) FROM $class_agent e")->getSingleScalarResult();
$num_verified_agents = $em->createQuery("SELECT COUNT(e) FROM $class_agent e WHERE e.isVerified = TRUE")->getSingleScalarResult();

$num_spaces = $em->createQuery("SELECT COUNT(e) FROM $class_space e")->getSingleScalarResult();
$num_verified_spaces = $em->createQuery("SELECT COUNT(e) FROM $class_space e WHERE e.isVerified = TRUE")->getSingleScalarResult();

$num_project = $em->createQuery("SELECT COUNT(e) FROM $class_project e")->getSingleScalarResult();
$num_verified_projects = $em->createQuery("SELECT COUNT(e) FROM $class_project e WHERE e.isVerified = TRUE")->getSingleScalarResult();



$event_linguagens = array_values($app->getRegisteredTaxonomy($class_event, 'linguagem')->restrictedTerms);
$project_linguagens = array_values($app->getRegisteredTaxonomy($class_project, 'linguagem')->restrictedTerms);
$agent_areas = array_values($app->getRegisteredTaxonomy($class_agent, 'area')->restrictedTerms);
$space_areas = array_values($app->getRegisteredTaxonomy($class_space, 'area')->restrictedTerms);

sort($event_linguagens);
sort($project_linguagens);
sort($agent_areas);
sort($space_areas);


$agent_types = $app->getRegisteredEntityTypes($class_agent);
$space_types = $app->getRegisteredEntityTypes($class_space);
$project_types = $app->getRegisteredEntityTypes($class_project);

$agent = $space = $event = $project = null;


$agent_ids = $em->createQuery("
 SELECT
    f.objectId as id
 FROM
    $class_file f
    JOIN $class_agent e WITH e.id = f.objectId
 WHERE 
    e.isVerified = TRUE AND
    f.objectType = '$class_agent' AND
    f.group = 'gallery'
")->getScalarResult();

if($agent_ids){
    $agent_id = $agent_ids[array_rand($agent_ids)]['id'];
    $agent = $app->repo('Agent')->find($agent_id);
    $agent_img_url = $agent->files['gallery'][array_rand($agent->files['gallery'])]->transform('galleryFull')->url;
}

$space_ids = $em->createQuery("
 SELECT
    f.objectId as id
 FROM
    $class_file f
    JOIN $class_space e WITH e.id = f.objectId
 WHERE 
    e.isVerified = TRUE AND
    f.objectType = '$class_space' AND
    f.group = 'gallery'
")->getScalarResult();

if($space_ids){
    $space_id = $space_ids[array_rand($space_ids)]['id'];
    $space = $app->repo('Space')->find($space_id);
    $space_img_url = $space->files['gallery'][array_rand($space->files['gallery'])]->transform('galleryFull')->url;
}

$event_ids = $em->createQuery("
 SELECT
    f.objectId as id
 FROM
    $class_file f
    JOIN $class_event e WITH e.id = f.objectId
 WHERE 
    e.isVerified = TRUE AND
    f.objectType = '$class_event' AND
    f.group = 'gallery'
")->getScalarResult();

if($event_ids){
    $event_id = $event_ids[array_rand($event_ids)]['id'];
    $event = $app->repo('Event')->find($event_id);
    $event_img_url = $event->files['gallery'][array_rand($event->files['gallery'])]->transform('galleryFull')->url;
}

$project_ids = $em->createQuery("
 SELECT
    f.objectId as id
 FROM
    $class_file f
    JOIN $class_project e WITH e.id = f.objectId
 WHERE 
    e.isVerified = TRUE AND
    f.objectType = '$class_project' AND
    f.group = 'gallery'
")->getScalarResult();

if($project_ids){
    $project_id = $project_ids[array_rand($project_ids)]['id'];
    $project = $app->repo('Project')->find($project_id);
    $project_img_url = $project->files['gallery'][array_rand($project->files['gallery'])]->transform('galleryFull')->url;
}
?>
<section id="capa-marca-dagua">

</section>
<section id="capa-intro" class="js-page-menu-item objeto-capa clearfix fundo-laranja">
    <div class="box">
        <h1>Bem-vind@!</h1>
        <p>O SP Cultura é a <a href="#capa-desenvolvedores">plataforma livre</a>, gratuita e colaborativa de mapeamento da cultura paulistana da Secretaria Municipal de Cultura de São Paulo onde você pode consultar <a href="#capa-eventos">agenda de eventos</a>, <a href="#capa-agentes">perfis de agentes culturais</a>, páginas com informações dos <a href="#capa-espacos">espaços culturais</a> ou dos <a href="#capa-projetos">projetos</a>, como editais em andamento.</p>
        <form id="form-de-busca-geral" class="clearfix">
            <input class="campo-de-busca" type="text" name="campo-de-busca" placeholder="Digite uma palavra-chave" />
            <div id="filtro-da-capa" class="dropdown">
                <div class="placeholder"><span class="icone icon_search"></span> Buscar</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li id="filtro-de-eventos"><span class="icone icon_calendar"></span> Eventos</li>
                        <li id="filtro-de-agentes"><span class="icone icon_profile"></span> Agentes</li>
                        <li id="filtro-de-espacos"><span class="icone icon_building"></span> Espaços</li>
                        <li id="filtro-de-projetos"><span class="icone icon_document_alt"></span> Projetos</li>
                    </ul>
                </div>
            </div>
        </form>
        <p>Você também pode participar colaborando com suas próprias informações, preenchendo seu perfil de agente (artistas ou gestores) e assim obter vantagens como, por exemplo, se candidatar facilmente em editais da prefeitura, além de poder disponibilizar seus eventos na agenda, divulgar seu espaço ou projeto. Para isso, é necessário ter uma conta no SPCultura, clicando no botão abaixo.</p>
        <p class="textcenter"><a class="botao-grande" href="<?php echo $app->createUrl('panel') ?>">Colabore com o SP Cultura</a></p>
    </div>
    <div class="ver-mais"><a class="hltip icone arrow_carrot-down" href="#capa-eventos" title="Saiba mais"></a></div>
</section>
<?php if($event): ?>
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
            <p>
                Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.
            </p>
            <h4>Encontre eventos por</h4>
            <ul class="abas clearfix">
                <li class="active"><a href="#event-terms">Linguagem</a></li>
            </ul>
            <div id="event-terms" class="tags">
                <?php foreach ($event_linguagens as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="box">
            <div class="destaque-aleatorio">
                <a class="destaque-content" href="#">
                    <h3>destaque</h3>
                    <h2>Título da entidade</h2>
                    <p>Descrição curta Casamentiss faiz malandris se pirulitá. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.</p>
                </a>
                <img src="<?php echo $assetURL ?>/img/gemeos.jpg" />
            </div>
            <a class="botao-grande" href="<?php echo $app->createUrl('event') ?>">Ver Todos Eventos</a>
            <a class="botao-grande adicionar" href="<?php echo $app->createUrl('event', 'create') ?>">Adicionar Eventos</a>
        </div>
    </article>
<?php endif; ?>
<?php if($agent): ?>
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
            <p>
                Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.
            </p>
            <h4>Encontre agentes por</h4>
            <ul class="abas clearfix">
                <li class="active"><a href="#agent-terms">Área de atuação</a></li>
                <li><a href="#agent-types">Tipo</a></li>
            </ul>
            <div id="agent-terms" class="tags">
                <?php foreach ($agent_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(areas:!(<?php echo $i ?>)),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
            <div id="agent-types" class="tags">
                <?php foreach ($agent_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(type:<?php echo $t->id ?>),global:(enabled:(agent:!t),filterEntity:agent))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="box">
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
            <a class="botao-grande" href="<?php echo $app->createUrl('site', 'search') ?>##(global:(enabled:(agent:!t),filterEntity:agent))">Ver Todos Agentes</a>
            <a class="botao-grande adicionar" href="<?php echo $app->createUrl('agent', 'create') ?>">Adicionar Agentes</a>
        </div>
    </article>
<?php endif; ?>
<?php if($space): ?>
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
            <p>
                Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.
            </p>
            <h4>Encontre espaços por</h4>
            <ul class="abas clearfix">
                <li class="active"><a href="#space-terms">Área de atuação</a></li>
                <li><a href="#space-types">Tipo</a></li>
            </ul>
            <div id="space-terms" class="tags">
                <?php foreach ($space_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(areas:!(<?php echo $i ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
            <div id="space-types" class="tags">
                <?php foreach ($space_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(types:!(<?php echo $t->id ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>

        </div>
        <div class="box">
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
            <a class="botao-grande" href="<?php echo $app->createUrl('space') ?>">Ver Todos Espaços</a>
            <a class="botao-grande adicionar" href="<?php echo $app->createUrl('space', 'create') ?>">Adicionar Espaços</a>
        </div>
    </article>
<?php endif; ?>
<?php if($project): ?>
    <article id="capa-projetos" class="js-page-menu-item objeto-capa clearfix fundo-vermelho">
        <div class="box">
            <h1><span class="icone icon_document_alt"></span> Projetos</h1>
            <div class="clearfix">
                <div class="estatisticas">
                    <div class="estatistica"><?php echo $num_spaces; ?></div>
                    <div class="label-das-estatisticas">projetos cadastrados</div>
                </div>
                <div class="estatisticas">
                    <div class="estatistica"><?php echo $num_verified_projects; ?></div>
                    <div class="label-das-estatisticas">projetos da SMC</div>
                </div>
            </div>
            <p>
                Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.
            </p>
            <h4>Encontre projetos por</h4>
            <ul class="abas clearfix">
                <li class="active"><a href="#project-terms">Linguagem</a></li>
                <li><a href="#project-types">Tipo</a></li>
            </ul>
            <div id="project-terms" class="tags">
                <?php foreach ($project_linguagens as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(linguagens:!(<?php echo $i ?>)),global:(enabled:(project:!t),filterEntity:project,viewMode:list))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
            <div id="project-types">
                <?php foreach ($project_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(types:!(<?php echo $t->id ?>)),global:(enabled:(project:!t),filterEntity:project,viewMode:list))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="box">
            <div class="destaque-aleatorio">
                <a class="destaque-content" href="#">
                    <h3>destaque</h3>
                    <h2>Título da entidade</h2>
                    <p>Descrição curta Casamentiss faiz malandris se pirulitá. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.</p>
                </a>
                <img src="<?php echo $assetURL ?>/img/oficina.jpg" />
            </div>
            <a class="botao-grande" href="<?php echo $app->createUrl('project') ?>">Ver Todos Projetos</a>
            <a class="botao-grande adicionar" href="<?php echo $app->createUrl('project', 'create') ?>">Adicionar Projetos</a>
        </div>
    </article>
<?php endif; ?>
<article id="capa-desenvolvedores" class="js-page-menu-item objeto-capa clearfix fundo-roxo">
    <div class="box">
        <h1><span class="icone icon_tools"></span> Desenvolvedores</h1>
        <p>
            Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis.
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