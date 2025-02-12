<?php

// Se a configuração de captcha não existir, significa que o captcha não foi implementado
if (!isset($app->config['captcha'])) {
    return;
}

if (!isset($app->_config['captcha']['providers'])) {
    throw new \Exception('Configuração de captcha inválida');
}

$provider = $app->_config['captcha']['provider'];
$captcha = $app->_config['captcha']['providers'][$provider];

// Importando a biblioteca do captcha
$app->view->enqueueScript('components', 'captcha', $captcha['url']);

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
