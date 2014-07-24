<?php
    $date_from = new DateTime();
    $date_to = new DateTime('+30 days');
    //var_dump($entity->className);
    if($entity->className ===  'MapasCulturais\Entities\Space'){

        $events = !$entity->id ? array() : $app->repo('Event')->findBySpace($entity, $date_from, $date_to);

    }elseif($entity->className ===  'MapasCulturais\Entities\Agent'){

        $events = !$entity->id ? array() : $app->repo('Event')->findByAgent($entity, $date_from, $date_to);

    }elseif($entity->className ===  'MapasCulturais\Entities\Project'){

        $events = !$entity->id ? array() : $app->repo('Event')->findByProject($entity, $date_from, $date_to);

    }else{
        $events = array();
    }
?>
<?php $this->part('parts/agenda-header', array('date_from'=>$date_from, 'date_to'=>$date_to, 'events_count'=>count($events))); ?>
<div id="agenda-content">
    <?php $this->part('parts/agenda-content', array('events' => $events, 'entity' => $entity)); ?>
</div>