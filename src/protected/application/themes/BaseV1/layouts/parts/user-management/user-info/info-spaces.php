<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Space;
  $current_user = $app->user;
?>

  <table class="spaces-table entity-table">
    <caption>
        <?php echo \MapasCulturais\i::_e("Espaços");?>
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
      <?php foreach($spaces as $space): ?>
      <tr>
        <td class="fit">
          <?php echo $space->id;?>
        </td>
        <td><a href="<?php echo $space->singleUrl;?>"><?php echo $space->name;?></a></td>
        <td><?php echo $space->subsite?$space->subsite->name:'';?></td>
        <td class="fit">
          <div class="entity-actions">
              <?php if($space->status === Space::STATUS_ENABLED): ?>
                  <a class="btn btn-small btn-danger" href="<?php echo $space->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" href="<?php echo $space->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

              <?php elseif ($space->status === Space::STATUS_DRAFT): ?>
                  <a class="btn btn-small btn-warning" href="<?php echo $space->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                  <a class="btn btn-small btn-danger" href="<?php echo $space->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

              <?php elseif ($space->status === Space::STATUS_ARCHIVED): ?>
                  <a class="btn btn-small btn-success" href="<?php echo $space->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

              <?php else: ?>
                  <a class="btn btn-small btn-success" href="<?php echo $space->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                  <?php if($space->isUserAdmin($current_user, 'superAdmin') ): ?>
                      <a class="btn btn-small btn-danger" href="<?php echo $space->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                  <?php endif; ?> 
              <?php endif; ?>
              
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
