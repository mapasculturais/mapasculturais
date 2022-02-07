<?php
namespace UserManagement\Controllers;

use MapasCulturais\Traits;

class SystemRole extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerAPI,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft;
}