<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Opportunity;
  $current_user = $app->user;
?>

  <table class="projects-table entity-table">
    <caption>
        <?php echo \MapasCulturais\i::_e("Minhas oportunidades");?>
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
      <?php foreach($opportunities as $opportunity): ?>
      <tr>
        <td class="fit">
          <?php echo $opportunity->id;?>
        </td>
        <td><a href="<?php echo $opportunity->singleUrl;?>"><?php echo $opportunity->name;?></a></td>
        <td><?php echo $opportunity->subsite?$opportunity->subsite->name:'';?></td>
        <td class="fit">
          <div class="entity-actions">
              <?php if($opportunity->status === Opportunity::STATUS_ENABLED): ?>
                  <a class="btn btn-small btn-danger" href="<?php echo $opportunity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" href="<?php echo $opportunity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

              <?php elseif ($opportunity->status === Opportunity::STATUS_DRAFT): ?>
                  <a class="btn btn-small btn-warning" href="<?php echo $opportunity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                  <a class="btn btn-small btn-danger" href="<?php echo $opportunity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

              <?php elseif ($opportunity->status === Opportunity::STATUS_ARCHIVED): ?>
                  <a class="btn btn-small btn-success" href="<?php echo $opportunity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

              <?php else: ?>
                  <a class="btn btn-small btn-success" href="<?php echo $opportunity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                  <?php if($opportunity->isUserAdmin($current_user, 'superAdmin') ): ?>
                      <a class="btn btn-small btn-danger" href="<?php echo $opportunity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                  <?php endif; ?> 
              <?php endif; ?>
              
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
