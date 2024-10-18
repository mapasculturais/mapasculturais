<?php

// Se a configuração de captcha não existir, significa que o captcha não foi implementado
if (!isset($app->config['captcha']) && !isset($app->_config['app.recaptcha.key'])) {
    return;
}

// Se não houver configuração nova de captcha, mas houver configuração antiga, vamos utilizar a configuração antiga
if (!isset($app->_config['captcha']) && isset($app->_config['app.recaptcha.key']) && isset($app->_config['app.recaptcha.secret'])) {
    return $this->jsObject['mcCaptchaConfig'] = [
        'captcha' => [
            'provider' => 'google',
            'key' => $app->_config['app.recaptcha.key'],
            'secret' => $app->_config['app.recaptcha.secret']
        ]
    ];
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
