<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Registration;

$registrations = Registration::getStatusesNames();

foreach ($registrations as $status => $status_name) {
    if (in_array($status, [0, 1, 2, 3, 8, 10])) {
        $data["registrationStatusDict"][] = ["label" => $status_name, "value" => $status];
    }
}

$this->jsObject['config']['evaluationMethodTechnicalApply'] = $data;
