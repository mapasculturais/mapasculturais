<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Event;
?>


<table class="events-table entity-table">
  <caption>
    <?=\MapasCulturais\i::_e("Eventos");?>
  </caption>
  <thead>
    <tr>
      <td>id</td>
      <td>Nome</td>
      <td>Operações</td>
      <!-- <td>Subsite</td> -->
    </tr>
  </thead>
  
  <tbody>
  <?php foreach($events as $event): ?>
    <tr>
      <td>
        <a href="<?=$event->singleUrl?>" class="icon icon-event"></a>
        <a href="<?=$event->singleUrl?>"><?=$event->id?></a>
      </td>
      <td><?=$event->name?></td>
      <td class="fit">
        <div class="entity-actions">
          <?php if($event->status === Event::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $event->unpublishUrl; ?>"><?php \MapasCulturais\i::_e("tornar rascunho");?></a>
            <a class="btn btn-small btn-danger"  href="<?php echo $event->deleteUrl; ?> ">  <?php \MapasCulturais\i::_e("excluir");?></a>
            <a class="btn btn-small btn-success" href="<?php echo $event->archiveUrl; ?>">  <?php \MapasCulturais\i::_e("arquivar");?></a>

          <?php elseif ($event->status === Event::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $event->publishUrl; ?>">  <?php \MapasCulturais\i::_e("publicar");?></a>
            <a class="btn btn-small btn-danger"  href="<?php echo $event->deleteUrl; ?>">   <?php \MapasCulturais\i::_e("excluir");?></a>

          <?php elseif ($event->status === Event::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $event->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>
          <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $event->undeleteUrl; ?>"> <?php \MapasCulturais\i::_e("recuperar");?></a>
            <?php if($event->permissionTo->destroy): ?>
              <a class="btn btn-small btn-danger" href="<?php echo $event->destroyUrl; ?>"> <?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>


  


