<?php
$entity = $this->controller->requestedEntity;

$isAUth = $app->user->is('guest') ? false : true;

if($isAUth){
    $emailTypes = ["emailPrivado", "emailPublico"];

    $ownerEmail = $app->user->email;
    foreach($emailTypes as $type){
        if($email = $app->user->profile->$type){
            $ownerEmail = $email;
            break;
        }
    }
}

$config = [
    'isAuth' => $isAUth,
    'senderName' => $isAUth ? $app->user->profile->name : "",
    'senderEmail' => $isAUth ? $ownerEmail : "",
];

$this->jsObject['complaintSuggestionConfig'] = $config;
$this->jsObject['notification_type'] = $app->getRegisteredMetadata('MapasCulturais\Entities\Notification');
