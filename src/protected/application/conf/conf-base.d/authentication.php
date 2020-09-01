<?php

return [
    'auth.provider' => 'OpauthKeyCloak', //\MultipleLocalAuth\Provider - 
    'auth.config' => array(
        'logout_url' => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/logout',
        'client_id' => 'MapaDigitalSaude',
        'client_secret' => '5fdc21eb-8b27-4e93-adf0-88e5ba4fe454',
	    'auth_endpoint' => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/auth',
        'token_endpoint'        => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/token',
        'user_info_endpoint'    => 'https://dev.id.org.br/auth/realms/saude/protocol/openid-connect/userinfo',
        'redirect_uri'          => 'http://localhost/autenticacao/keycloak/oauth2callback',
        ),
    // 'auth.config' => [
    //     'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
    //     'timeout' => '24 hours',
    //     'enableLoginByCPF' => true,
    //     'passwordMustHaveCapitalLetters' => true,
    //     'passwordMustHaveLowercaseLetters' => true,
    //     'passwordMustHaveSpecialCharacters' => true,
    //     'passwordMustHaveNumbers' => true,
    //     'minimumPasswordLength' => 6,
    //     'google-recaptcha-secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    //     'google-recaptcha-sitekey' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    //     'sessionTime' => 7200, // int , tempo da sessao do usuario em segundos
    //     'numberloginAttemp' => '5', // tentativas de login antes de bloquear o usuario por X minutos
    //     'timeBlockedloginAttemp' => '900', // tempo de bloqueio do usuario em segundos
    //     'strategies' => [
    //         'Facebook' => [
    //                'app_id' => 'SUA_APP_ID',
    //             'app_secret' => 'SUA_APP_SECRET',
    //             'scope' => 'email'
    //         ],
    //         'LinkedIn' => [
    //             'api_key' => 'SUA_API_KEY',
    //             'secret_key' => 'SUA_SECRET_KEY',
    //             'redirect_uri' => '/autenticacao/linkedin/oauth2callback',
    //             'scope' => 'r_emailaddress'
    //         ],
    //         'Google' => [
    //             'client_id' => 'SEU_CLIENT_ID',
    //             'client_secret' => 'SEU_CLIENT_SECRET',
    //             'redirect_uri' => '/autenticacao/google/oauth2callback',
    //             'scope' => 'email'
    //         ],
    //         'Twitter' => [
    //             'app_id' => 'SUA_APP_ID',
    //             'app_secret' => 'SUA_APP_SECRET',
    //         ],
    //     ]
    // ],

];
