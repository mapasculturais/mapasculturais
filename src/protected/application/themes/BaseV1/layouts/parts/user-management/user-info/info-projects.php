<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Project;
  $current_user = $app->user;
?>

  <table class="projects-table entity-table">
    <caption>
        <?php echo \MapasCulturais\i::_e("Projetos");?>
    </caption>
    <thead>
      <tr>
        <td><?php \MapasCulturais\i::_e("id");?></td>
        <td><?php \MapasCulturais\i::_e("Nome");?></td>
        <td><?php \MapasCulturais\i::_e("Subsite");?></td>
        <td><?php \MapasCulturais\i::_e("Operações");?></td>
      </tr>
    </thead>
    <tbody>
      <?php foreach($projects as $project): ?>
      <tr>
        <td class="fit">
          <?php echo $project->id;?>
        </td>
        <td><a href="<?php echo $project->singleUrl;?>"><?php echo $project->name;?></a></td>
        <td><?php echo $project->subsite?$project->subsite->name:'';?></td>
        <td class="fit">
          <div class="entity-actions">
              <?php if($project->status === Project::STATUS_ENABLED): ?>
                  <a class="btn btn-small btn-danger" href="<?php echo $project->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" href="<?php echo $project->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

              <?php elseif ($project->status === Project::STATUS_DRAFT): ?>
                  <a class="btn btn-small btn-warning" href="<?php echo $project->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                  <a class="btn btn-small btn-danger" href="<?php echo $project->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

              <?php elseif ($project->status === Project::STATUS_ARCHIVED): ?>
                  <a class="btn btn-small btn-success" href="<?php echo $project->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

              <?php else: ?>
                  <a class="btn btn-small btn-success" href="<?php echo $project->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                  <?php if($project->isUserAdmin($current_user, 'superAdmin') ): ?>
                      <a class="btn btn-small btn-danger" href="<?php echo $project->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                  <?php endif; ?> 
              <?php endif; ?>
              
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
