<?php 

if(!$app->isEnabled('projects')){
    return;
}

$class_project = 'MapasCulturais\Entities\Project';
$class_file = 'MapasCulturais\Entities\File';
$num_projects           = $this->getNumEntities($class_project);
$num_verified_projects  = $this->getNumEntities($class_project, true);

$project_types = $app->getRegisteredEntityTypes($class_project);

$project = $this->getOneVerifiedEntity($class_project);
if($project && $img_url = $this->getEntityFeaturedImageUrl($project)){
    $project_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_projects = $this->searchProjectsUrl;

$project_img_attributes = 'class="random-feature no-image"';
?>
<article id="home-projects" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_projects; ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("projetos cadastrados");?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_projects; ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("projetos da ");?><?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: projects') ?></p>
        <h4><?php \MapasCulturais\i::_e("Encontre projetos por");?></h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#project-types"><?php \MapasCulturais\i::_e("Tipo");?></a></li>
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
                        <h3><?php \MapasCulturais\i::_e("destaque");?></h3>
                        <h2><?php echo $project->name ?></h2>
                        <p><?php echo $project->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('project', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar projeto");?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_projects ?>"><?php \MapasCulturais\i::_e("Ver tudo");?></a>
    </div>
</article>
