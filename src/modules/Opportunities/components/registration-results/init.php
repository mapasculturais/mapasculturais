<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

use MapasCulturais\Entities\Registration;

$entity = $this->controller->requestedEntity;

// SOLUÇÃO TEMPORÁRIA
$class = $entity->getClassName();
if($class == Registration::class) {

    $registration = $this->controller->requestedEntity;
    $registration_number = $registration->number;
    
    $all_registrations = $app->repo('Registration')->findBy(['number' => $registration_number]);
    $registrations = [];

    $result = [];
    foreach($all_registrations as $reg) {
        if ($em = $reg->evaluationMethod) {
            $result[$reg->id] = $em->shouldDisplayEvaluationResults($reg);
        }
    }
   
    
    $this->jsObject['config']['registrationResults']['shouldDisplayEvaluationResults'] = $result;
}