<?php
namespace UserManagement\Controllers;

use MapasCulturais\Traits;

class UserManagement extends \MapasCulturais\Controllers\EntityController {
    use Traits\ControllerAPI,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft;
    
    public function __construct()
    {
        parent::__construct();
        $this->entityClassName = "MapasCulturais\\Entities\\User";
    }

    
}