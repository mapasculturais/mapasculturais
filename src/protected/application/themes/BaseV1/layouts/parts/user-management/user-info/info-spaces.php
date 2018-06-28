<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Space;
?>

  <table class="spaces-table entity-table">
    <caption> 
        <?=\MapasCulturais\i::_e("Espaços");?>
    </caption>
    <thead>
      <tr>
        <td>id</td>
        <td>Nome</td>
        <td>Subsite</td>
        <td>Operações</td>
      </tr>
    </thead>
    <tbody>
      <?php foreach($spaces as $space): ?>
      <tr>
        <td>
          <a href="<?=$space->singleUrl?>" class="icon icon-space"></a>
          <a href="<?=$space->singleUrl?>"><?=$space->id?></a>
        </td>
        <td><?=$space->name?></td>
        <td class="fit"><?=$space->subsite?$space->subsite->name:'';?></td>

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
                  <?php if($space->permissionTo->destroy): ?>
                      <a class="btn btn-small btn-danger" href="<?php echo $space->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                  <?php endif; ?>
              <?php endif; ?>
              
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
