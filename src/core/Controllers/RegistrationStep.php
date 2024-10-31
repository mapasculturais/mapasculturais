<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;

class RegistrationStep extends \MapasCulturais\Controller {
    use Traits\ControllerEntity,
        Traits\ControllerEntityActions,
        Traits\ControllerAPI;

    protected function __construct() {
        $this->entityClassName =  'MapasCulturais\Entities\RegistrationStep';
    }
}
