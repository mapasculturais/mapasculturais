<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Registration;
$entity = $this->controller->requestedEntity;
$em = $app->em;
$conn = $em->getConnection();
$dql = "SELECT MAX(e.consolidatedResult) FROM MapasCulturais\\Entities\\Registration e WHERE e.opportunity = {$entity->id}";
$query = $app->em->createQuery($dql);
$data['max_result'] = $query->getSingleScalarResult();

$registrations = Registration::getStatusesNames();
foreach ($registrations as $status => $status_name) {
    if (in_array($status, [0, 1, 2, 3, 8, 10])) {
        $data["registrationStatusDict"][] = ["label" => $status_name, "value" => $status];
    }
}

$this->jsObject['config']['evaluationMethodTechnicalApply'] = $data;
