<?php
namespace UserManagement\Controllers;

use MapasCulturais\Traits;

class UserManagement extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerAPI,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft;
}