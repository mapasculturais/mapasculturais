<?php
$app = \MapasCulturais\App::i();
$em = $app->em;

$class_event = 'MapasCulturais\Entities\Event';
$class_agent = 'MapasCulturais\Entities\Agent';
$class_space = 'MapasCulturais\Entities\Space';
$class_project = 'MapasCulturais\Entities\Project';

$class_file = 'MapasCulturais\Entities\File';

$num_events = $em->createQuery("SELECT COUNT(e) FROM $class_event e WHERE e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();
$num_verified_events = $em->createQuery("SELECT COUNT(e) FROM $class_event e WHERE e.isVerified = TRUE AND e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();

$num_agents = $em->createQuery("SELECT COUNT(e) FROM $class_agent e WHERE e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();
$num_verified_agents = $em->createQuery("SELECT COUNT(e) FROM $class_agent e WHERE e.isVerified = TRUE AND e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();

$num_spaces = $em->createQuery("SELECT COUNT(e) FROM $class_space e WHERE e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();
$num_verified_spaces = $em->createQuery("SELECT COUNT(e) FROM $class_space e WHERE e.isVerified = TRUE AND e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();

$num_projects = $em->createQuery("SELECT COUNT(e) FROM $class_project e WHERE e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();
$num_verified_projects = $em->createQuery("SELECT COUNT(e) FROM $class_project e WHERE e.isVerified = TRUE AND e.status > 0")->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getSingleScalarResult();



$event_linguagens = array_values($app->getRegisteredTaxonomy($class_event, 'linguagem')->restrictedTerms);
$agent_areas = array_values($app->getRegisteredTaxonomy($class_agent, 'area')->restrictedTerms);
$space_areas = array_values($app->getRegisteredTaxonomy($class_space, 'area')->restrictedTerms);

sort($event_linguagens);
sort($agent_areas);
sort($space_areas);


$agent_types = $app->getRegisteredEntityTypes($class_agent);
$space_types = $app->getRegisteredEntityTypes($class_space);
$project_types = $app->getRegisteredEntityTypes($class_project);



/**
 * Returns a verified entity with images in gallery
 * @param type $entity_class
 * @return \MapasCulturais\Entity
 */
function findOneVerifiedEntityWithImages($entity_class){
    $app = \MapasCulturais\App::i();

    $file_class = 'MapasCulturais\Entities\File';

    $dql = "
     SELECT
        DISTINCT f.objectId as id
     FROM
        $file_class f
        JOIN $entity_class e WITH e.id = f.objectId
     WHERE
        e.status > 0 AND
        e.isVerified = TRUE AND
        f.objectType = '$entity_class' AND
        f.group = 'gallery'
    ";


    $ids = $app->em->createQuery($dql)->useQueryCache(true)->setResultCacheLifetime(60 * 5)->getScalarResult();
    if($ids){
        $id = $ids[array_rand($ids)]['id'];
        return $app->repo($entity_class)->find($id);
    }else{
        return null;
    }
}

$agent = findOneVerifiedEntityWithImages($class_agent);
if($agent)
    $agent_img_url = $agent->files['gallery'][array_rand($agent->files['gallery'])]->transform('galleryFull')->url;

$space = findOneVerifiedEntityWithImages($class_space);
if($space)
    $space_img_url = $space->files['gallery'][array_rand($space->files['gallery'])]->transform('galleryFull')->url;

$event = findOneVerifiedEntityWithImages($class_event);
if($event)
    $event_img_url = $event->files['gallery'][array_rand($event->files['gallery'])]->transform('galleryFull')->url;

$project = findOneVerifiedEntityWithImages($class_project);
if($project)
    $project_img_url = $project->files['gallery'][array_rand($project->files['gallery'])]->transform('galleryFull')->url;


$url_search_agents = $app->createUrl('site', 'search')."##(global:(enabled:(agent:!t),filterEntity:agent))";
$url_search_spaces = $app->createUrl('site', 'search')."##(global:(enabled:(space:!t),filterEntity:space))";
$url_search_events = $app->createUrl('site', 'search')."##(global:(enabled:(event:!t),filterEntity:event))";
$url_search_projects = $app->createUrl('site', 'search')."##(global:(filterEntity:project,viewMode:list))";

?>
<section id="capa-marca-dagua">

</section>
<section id="capa-intro" class="js-page-menu-item objeto-capa clearfix fundo-laranja">
    <div class="box">
        <h1>Bem-vind@!</h1>
        <p>O SP Cultura é a plataforma livre, gratuita e colaborativa de mapeamento da Secretaria Municipal de Cultura de São Paulo sobre o cenário cultural paulistano. Ficou mais fácil se programar para conhecer as opções culturais que a cidade oferece: shows musicais, espetáculos teatrais, sessões de cinema, saraus, entre outras. Além de conferir a agenda de eventos, você também pode colaborar na gestão da cultura da cidade: basta criar seu perfil de <a href="<?php echo $url_search_agents ?>" >agente cultural</a>. A partir deste cadastro, fica mais fácil participar dos editais de fomento às artes da Prefeitura e também divulgar seus <a href="<?php echo $url_search_events; ?>">eventos</a>, <a href="<?php echo $url_search_spaces; ?>">espaços</a> ou <a href="<?php echo $url_search_projects; ?>">projetos</a>.</p>
        <form id="form-de-busca-geral" class="clearfix">
            <input id="campo-de-busca" class="campo-de-busca" type="text" name="campo-de-busca" placeholder="Digite uma palavra-chave" />
            <div id="filtro-da-capa" class="dropdown" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}}),{{entity}}:(keyword:'{{keyword}}'))">
                <div class="placeholder"><span class="icone icon_search"></span> Buscar</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li id="filtro-de-eventos"  data-entity="event"><span class="icone icon_calendar"></span> Eventos</li>
                        <li id="filtro-de-agentes"  data-entity="agent"><span class="icone icon_profile"></span> Agentes</li>
                        <li id="filtro-de-espacos"  data-entity="space"><span class="icone icon_building"></span> Espaços</li>
                        <li id="filtro-de-projetos" data-entity="project" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}},viewMode:list),{{entity}}:(keyword:'{{keyword}}'))"><span class="icone icon_document_alt"></span> Projetos</li>
                    </ul>
                </div>
            </div>
        </form>
        <p class="textcenter"><a class="botao-grande" href="<?php echo $app->createUrl('panel') ?>">Colabore com o SP Cultura</a></p>
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
        <p>Você pode pesquisar eventos culturais da cidade nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.</p>
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
        <a class="botao-grande" href="<?php echo $url_search_events ?>">Ver Todos Eventos de Hoje</a>
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
        <p>Você pode colaborar na gestão da cultura da cidade com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural paulistana. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.</p>
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
        <p>Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais na cidade.</p>
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
        <p>Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criado pela Secretaria Municipal de Cultura, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.</p>
        <h4>Encontre projetos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#project-types">Tipo</a></li>
        </ul>
        <div id="project-types"  class="tags">
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
             Existem algumas maneiras de desenvolvedores interagirem com o SP Cultura. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o SP Cultura é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">Github</a>.
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
