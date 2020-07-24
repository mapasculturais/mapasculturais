<?php
return [
    'auth.provider' => 'Fake',
    'auth.config' => [],
    /*
    // https://github.com/kterva/MultipleLocalAuth
    'auth.provider' => '\MultipleLocalAuth\Provider',

    'auth.config' => [
        'salt' => env('AUTH_SALT', 'SECURITY_SALT'),
        'timeout' => '24 hours',
        'strategies' => [
           'Facebook' => [
               'app_id' => env('AUTH_FACEBOOK_APP_ID', null),
               'app_secret' => env('AUTH_FACEBOOK_APP_SECRET', null),
               'scope' => env('AUTH_FACEBOOK_SCOPE', 'email'),
            ],
            
            'Google' => [
                'client_id' => env('AUTH_GOOGLE_CLIENT_ID', null),
                'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET', null),
                'redirect_uri' => APP_BASE_URL . 'autenticacao/google/oauth2callback',
                'scope' => env('AUTH_GOOGLE_SCOPE', 'email'),
            ],

            'LinkedIn' => [
                'api_key' => env('AUTH_LINKEDIN_API_KEY', null),
                'secret_key' => env('AUTH_LINKEDIN_SECRET_KEY', null),
                'redirect_uri' => APP_BASE_URL . 'autenticacao/linkedin/oauth2callback',
                'scope' => env('AUTH_LINKEDIN_SCOPE', 'r_emailaddress')
            ],

            'Twitter' => [
                'app_id' => env('AUTH_TWITTER_APP_ID', null),
                'app_secret' => env('AUTH_TWITTER_APP_SECRET', null),
            ]
        ]
    ]
    */
];
