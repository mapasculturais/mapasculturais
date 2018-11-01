<?php
switch(env('AUTH_PROVIDER', 'FAKE')) {
    case 'MULTIPLELOCAL':
        return [
            'auth.provider' => '\MultipleLocalAuth\Provider',
            'auth.config' => [
                'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
                'timeout' => '24 hours',
                'strategies' => [
                    'Facebook' => [
                        'app_id' => 'SUA_APP_ID',
                        'app_secret' => 'SUA_APP_SECRET',
                        'scope' => 'email'
                    ],
                    'LinkedIn' => [
                        'api_key' => 'SUA_API_KEY',
                        'secret_key' => 'SUA_SECRET_KEY',
                        'redirect_uri' => '/autenticacao/linkedin/oauth2callback',
                        'scope' => 'r_emailaddress'
                    ],
                    'Google' => [
                        'client_id' => 'SEU_CLIENT_ID',
                        'client_secret' => 'SEU_CLIENT_SECRET',
                        'redirect_uri' => '/autenticacao/google/oauth2callback',
                        'scope' => 'email'
                    ],
                    'Twitter' => [
                        'app_id' => 'SUA_APP_ID',
                        'app_secret' => 'SUA_APP_SECRET',
                    ]
                ]
            ]
        ];
        break;
    case 'LOGINCIDADAO':
        return [
            'auth.provider' => 'OpauthLoginCidadao',
            'auth.config' => [
                'client_id' => '',
                'client_secret' => '',
                'auth_endpoint' => 'https://[SUA-URL]/openid/connect/authorize',
                'token_endpoint' => 'https://[SUA-URL]/openid/connect/token',
                'user_info_endpoint' => 'https://[SUA-URL]/api/v1/person.json'
            ]
        ];
        break;
    default:
        return [
            'auth.provider' => 'Fake'
        ];
        break;
}