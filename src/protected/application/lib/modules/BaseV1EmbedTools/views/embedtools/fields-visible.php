<?php
    $previous_phases = $entity->previousPhases;

      if($entity->firstPhase->id != $entity->id){
          $previous_phases[] = $entity;
      }

      foreach($previous_phases as $phase)
      {
          foreach($phase->registrationFieldConfigurations as $field){
              $app->view->jsObject['evaluationFieldsList'][] = $field;
          }

          foreach($phase->registrationFileConfigurations as $file){
              $app->view->jsObject['evaluationFieldsList'][] = $file;
          }
      }
?>
<?php $this->part('singles/opportunity-evaluations-fields--config', ['entity' => $entity]) ?>
