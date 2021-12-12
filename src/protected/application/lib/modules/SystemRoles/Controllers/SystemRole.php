<?php
namespace SystemRoles\Controllers;

use MapasCulturais\Traits;

class SystemRole extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerAPI,
        Traits\ControllerSoftDelete;
}