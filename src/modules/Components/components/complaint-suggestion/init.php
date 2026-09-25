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

$disableContact = false;
if ($isAUth) {
    $birthDate = $app->user->profile->dataDeNascimento;
    if ($birthDate) {
        $today = new \DateTime('now');
        $birthDateTime = new \DateTime($birthDate);
        $age = $birthDateTime->diff($today)->y;
        $disableContact = $age < 18;
    }
}

$config = [
    'isAuth' => $isAUth,
    'senderName' => $isAUth ? $app->user->profile->name : "",
    'senderEmail' => $isAUth ? $ownerEmail : "",
    'disableContact' => $disableContact,
];

$this->jsObject['complaintSuggestionConfig'] = $config;
$this->jsObject['notification_type'] = $app->getRegisteredMetadata('MapasCulturais\Entities\Notification');
