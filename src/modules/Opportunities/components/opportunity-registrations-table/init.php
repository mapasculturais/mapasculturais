<?php

use MapasCulturais\Entities\Registration;

$registrations = Registration::getStatusesNames();

foreach($registrations as $status => $status_name){
    if(in_array($status,[0,1,2,3,8,10])){
        $data[] = ["label" => $status_name, "value" => $status];
    }
}

$this->jsObject['config']['opportunityRegistrationTable'] = $data;