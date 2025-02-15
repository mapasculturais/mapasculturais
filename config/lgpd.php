<?php
use MapasCulturais\i;
return [
    'module.LGPD' => [
        'termsOfUsage'=>[
            'title'=> 'Termos e Condições de Uso', 
            'text'=> file_get_contents(__DIR__ . '/lgpd-terms/terms-of-usage.html'),
        'buttonText' => i::__('Aceito os termos e condiçoes de uso')
        ],
        'privacyPolicy' => [
            'title'=>  'Política de Privacidade',
            'text'=> file_get_contents(__DIR__ . '/lgpd-terms/privacy-policy.html'),
            'buttonText' => i::__('Aceito a política de privacidade')
        ],
        'termsUse' => [
            'title'=>  'Autorização de Uso de Imagem',
            'text'=> file_get_contents(__DIR__ . '/lgpd-terms/images-use.html'),
            'buttonText' => i::__('Autorizo o uso de imagem')
        ],
    ]
];