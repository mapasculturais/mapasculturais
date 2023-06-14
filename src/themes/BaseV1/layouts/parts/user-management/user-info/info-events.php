<?php 
  use MapasCulturais\App;
  use MapasCulturais\Entities\Event;
  $current_user = $app->user;
?>


<table class="events-table entity-table">
  <caption>
    <?php echo \MapasCulturais\i::_e("Eventos");?>
  </caption>
  <thead>
    <tr>
      <td><?php \MapasCulturais\i::_e("id");?></td>
      <td><?php \MapasCulturais\i::_e("Nome");?></td>
      <td><?php \MapasCulturais\i::_e("Operações");?></td>
      <!-- <td>Subsite</td> -->
    </tr>
  </thead>
  
  <tbody>
  <?php foreach($events as $event): ?>
    <tr>
      <td class="fit">
        <?php echo $event->id;?>
      </td>
      <td>
        <a href="<?php echo $event->singleUrl;?>"><?php echo $event->name;?></a>
      </td>
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
            <?php if($event->isUserAdmin($current_user, 'superAdmin') ): ?>
              <a class="btn btn-small btn-danger" href="<?php echo $event->destroyUrl; ?>"> <?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>


  


