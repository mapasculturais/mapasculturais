<?php 
if($this->controller->action === 'create'){ 
    return;
}


$is_project = false;
$label = 'Projetos';

if($entity instanceof MapasCulturais\Entities\Project){
    $is_project = true;
    $label = 'Subprojetos';
}

?>

<div class="widget">
    <?php if ($projects): ?>
        <h3><?php echo $label ?></h3>
        <ul class="widget-list js-slimScroll">
            <?php foreach ($projects as $project): ?>
                <li class="widget-list-item"><a href="<?php echo $project->singleUrl; ?>"><span><?php echo $project->name; ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <?php if ($is_project && $entity->canUser('createChild')): ?>
        <a class="btn btn-default add" href="<?php echo $app->createUrl('project', 'create', array('parentId' => $entity->id)) ?>"><?php \MapasCulturais\i::_e("Adicionar subprojeto");?></a>
    <?php endif; ?>
</div>