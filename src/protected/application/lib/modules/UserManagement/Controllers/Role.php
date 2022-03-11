<?php
namespace UserManagement\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

class Role extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerAPI,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft;

    protected $entityClassName = "MapasCulturais\\Entities\\Role";


}