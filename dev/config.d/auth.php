<?php

return [
//    'auth.provider' => 'Fake',

    'auth.provider' => '\MultipleLocalAuth\Provider',
    'auth.config' => [
        'salt' => env('AUTH_SALT', null),
        'wizard' => env('AUTH_WIZARD_ENABLED', false),
        'timeout' => '24 hours',
        'strategies' => [
            'Facebook' => [
                'app_id' => env('AUTH_FACEBOOK_APP_ID', null),
                'app_secret' => env('AUTH_FACEBOOK_APP_SECRET', null),
                'scope' => env('AUTH_FACEBOOK_SCOPE', 'email'),
            ],
            'LinkedIn' => [
                'api_key' => env('AUTH_LINKEDIN_API_KEY', null),
                'secret_key' => env('AUTH_LINKEDIN_SECRET_KEY', null),
                'redirect_uri' => '/autenticacao/linkedin/oauth2callback',
                'scope' => env('AUTH_LINKEDIN_SCOPE', 'r_emailaddress')
            ],
            'Google' => [
                'client_id' => env('AUTH_GOOGLE_CLIENT_ID', null),
                'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET', null),
                'redirect_uri' => '/autenticacao/google/oauth2callback',
                'scope' => env('AUTH_GOOGLE_SCOPE', 'email'),
            ],
            'Twitter' => [
                'app_id' => env('AUTH_TWITTER_APP_ID', null),
                'app_secret' => env('AUTH_TWITTER_APP_SECRET', null),
            ],
            'govbr' => [
                'visible' => env('AUTH_GOV_BR_VISIBLE', false),
                'response_type' => env('AUTH_GOV_BR_RESPONSE_TYPE', 'code'),
                'client_id' => env('AUTH_GOV_BR_CLIENT_ID', null),
                'client_secret' => env('AUTH_GOV_BR_SECRET', null),
                'scope' => env('AUTH_GOV_BR_SCOPE', null),
                'redirect_uri' => env('AUTH_GOV_BR_REDIRECT_URI', null), 
                'auth_endpoint' => env('AUTH_GOV_BR_ENDPOINT', null),
                'token_endpoint' => env('AUTH_GOV_BR_TOKEN_ENDPOINT', null),
                'nonce' => env('AUTH_GOV_BR_NONCE', null),
                'code_verifier' => env('AUTH_GOV_BR_CODE_VERIFIER', null),
                'code_challenge' => env('AUTH_GOV_BR_CHALLENGE', null),
                'code_challenge_method' => env('AUTH_GOV_BR_CHALLENGE_METHOD', null),
                'userinfo_endpoint' => env('AUTH_GOV_BR_USERINFO_ENDPOINT', null),
                'state_salt' => env('AUTH_GOV_BR_STATE_SALT', null),
                'applySealId' => env('AUTH_GOV_BR_APPLY_SEAL_ID', null),
                'menssagem_authenticated' => env('AUTH_GOV_BR_MENSSAGEM_AUTHENTICATED','Usuário já se autenticou pelo GovBr'),
                'dic_agent_fields_update' => json_decode(env('AUTH_GOV_BR_DICT_AGENT_FIELDS_UPDATE', '{}'), true)
            ]
        ]
    ]
];
