<?php

if(!$app->isEnabled('agents')){
    return;
}

$class_agent = 'MapasCulturais\Entities\Agent';
$num_agents             = $this->getNumEntities($class_agent);
$num_verified_agents    = $this->getNumEntities($class_agent, true);
$agent_areas = array_values($app->getRegisteredTaxonomy($class_agent, 'area')->restrictedTerms);
sort($agent_areas);

$agent_types = $app->getRegisteredEntityTypes($class_agent);

$agent_img_attributes = 'class="random-feature no-image"';

$agent = $this->getOneVerifiedEntity($class_agent);
if($agent && $img_url = $this->getEntityFeaturedImageUrl($agent)){
    $agent_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_agents = $this->searchAgentsUrl;

?>

<article id="home-agents" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-agent"></span><?php \MapasCulturais\i::_e("Agentes");?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_agents ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("agentes cadastrados");?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_agents ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("agentes da ");?><?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: agents') ?></p>
        <h4><?php \MapasCulturais\i::_e("Encontre agentes por");?></h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#agent-terms"><?php \MapasCulturais\i::_e("Área de atuação");?></a></li>
            <li><a href="#agent-types"><?php \MapasCulturais\i::_e("Tipo");?></a></li>
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
                    <h3><?php \MapasCulturais\i::_e("destaque");?></h3>
                    <h2><?php echo $agent->name ?></h2>
                    <p><?php echo $agent->shortDescription ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('agent', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar agente");?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_agents ?>"><?php \MapasCulturais\i::_e("Ver tudo");?></a>
    </div>
</article>
