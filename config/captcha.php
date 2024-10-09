<?php
return [
    'app.recaptcha.key' => env("GOOGLE_RECAPTCHA_SITEKEY", null),
    'app.recaptcha.secret' => env("GOOGLE_RECAPTCHA_SECRET", null),

    /**
     * Configuração para implementação de captcha seguindo um novo padrão de configuração
     * 
     * - O exemplo abaixo segue o padrão de configuração para o Google Recaptcha atualmente utilizado
     * - Esse padrão de configuração permite a implementação de outros provedores de captcha, como por exemplo o Cloudflare Turnstile
     */
    'captcha' => [
        'provider' => 'google',
        'providers' => [
            'google' => [
                'url' => 'https://www.google.com/recaptcha/api.js',
                'verify' => 'https://www.google.com/recaptcha/api/siteverify',
                'key' => env('GOOGLE_RECAPTCHA_SITEKEY', null),
                'secret' => env('GOOGLE_RECAPTCHA_SECRET', null)
            ]
        ]
    ]
];
