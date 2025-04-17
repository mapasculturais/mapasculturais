<?php
return [
    /**
     * Configuração para implementação de captcha seguindo um novo padrão de configuração
     * 
     * - O exemplo abaixo segue o padrão de configuração para o Google Recaptcha atualmente utilizado
     * - Esse padrão de configuração permite a implementação de outros provedores de captcha, como por exemplo o Cloudflare Turnstile
     */
    'captcha' => [
        'provider' => env('CAPTCHA_PROVIDER', 'google'),
        'providers' => [
            'google' => [
                'url' => 'https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit',
                'verify' => 'https://www.google.com/recaptcha/api/siteverify',
                'key' => env('CAPTCHA_SITEKEY', env('GOOGLE_RECAPTCHA_SITEKEY', null)),
                'secret' => env('CAPTCHA_SECRET', env('GOOGLE_RECAPTCHA_SECRET', null))
            ],
            'cloudflare' => [
                'url' => 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
                'verify' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                'key' => env('CAPTCHA_SITEKEY', null),
                'secret' => env('CAPTCHA_SECRET', null)
            ]
        ]
    ]
];
