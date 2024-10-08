<?php

$provider = $app->_config['captcha']['provider'];
$captcha = $app->_config['captcha']['providers'][$provider];

$key = $captcha['key'];
$secret = $captcha['secret'];

$config = [
    'captcha' => [
        'provider' => $provider,
        'key' => $key,
        'secret' => $secret
    ]
];

$this->jsObject['mcCaptchaConfig'] = $config;
