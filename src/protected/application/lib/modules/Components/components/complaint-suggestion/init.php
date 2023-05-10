<?php
$config = [
    'recaptcha' => [
        'sitekey' =>  $app->_config['app.recaptcha.key'],
    ]
];
$this->jsObject['complaintSuggestionConfig'] = $config;
$this->jsObject['notification_type'] = $app->getRegisteredMetadata('MapasCulturais\Entities\Notification');
